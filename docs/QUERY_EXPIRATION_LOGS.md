# Query Expiration Logging Guide

## Overview

The `queries:check-expired` command now includes comprehensive logging to track all aspects of query expiration processing.

## Log Levels

- **INFO**: Normal operations, successful processing
- **WARNING**: Non-critical issues (e.g., notification skipped)
- **ERROR**: Failures that prevent query expiration

## Log Entries

### 1. Check Started
**When**: Command execution begins
```json
{
  "timestamp": "2025-12-30 23:52:49",
  "command": "queries:check-expired"
}
```

### 2. Queries Found
**When**: After querying database for expired queries
```json
{
  "total_expired": 1,
  "check_timestamp": "2025-12-30 23:52:49"
}
```

### 3. Expired Queries Details
**When**: When expired queries are found
```json
{
  "count": 1,
  "queries": [
    {
      "id": 153,
      "officer_id": 1376,
      "officer_name": "B.Z Adebayo",
      "service_number": "NCS11147",
      "issued_by": "staff.apapa@ncs.gov.ng",
      "deadline": "2025-12-30 23:40:00",
      "issued_at": "2025-12-30 22:38:30",
      "hours_overdue": -0.21
    }
  ]
}
```

### 4. Processing Expired Query
**When**: Before processing each query
```json
{
  "query_id": 153,
  "officer_id": 1376,
  "officer_name": "B.Z Adebayo",
  "deadline": "2025-12-30 23:40:00",
  "status_before": "PENDING_RESPONSE"
}
```

### 5. Notification Sent
**When**: Successfully sent notification to officer
```json
{
  "query_id": 153,
  "officer_id": 1376,
  "user_id": 1432,
  "user_email": "officer11147@ncs.gov.ng"
}
```

### 6. Notification Skipped (Warning)
**When**: Officer has no user account
```json
{
  "query_id": 153,
  "officer_id": 1376
}
```

### 7. Notification Failed (Warning)
**When**: Notification sending fails but query expiration continues
```json
{
  "query_id": 153,
  "officer_id": 1376,
  "error": "Error message"
}
```

### 8. Query Expired Successfully
**When**: Query successfully moved to ACCEPTED status
```json
{
  "query_id": 153,
  "officer_id": 1376,
  "officer_name": "B.Z Adebayo",
  "service_number": "NCS11147",
  "status_after": "ACCEPTED",
  "reviewed_at": "2025-12-30 23:52:49",
  "deadline": "2025-12-30 23:40:00",
  "hours_overdue": -0.21,
  "notification_sent": true,
  "execution_time_ms": 6190.25
}
```

### 9. Query Expiration Failed (Error)
**When**: Query expiration fails
```json
{
  "query_id": 153,
  "officer_id": 1376,
  "officer_name": "B.Z Adebayo",
  "deadline": "2025-12-30 23:40:00",
  "error": "Error message",
  "error_trace": "Stack trace..."
}
```

### 10. Check Completed
**When**: Command execution completes
```json
{
  "total_found": 1,
  "successfully_expired": 1,
  "failed": 0,
  "execution_time_ms": 6258.22,
  "timestamp": "2025-12-30 23:52:55"
}
```

### 11. No Expired Queries
**When**: No expired queries found
```json
{
  "execution_time_ms": 45.23,
  "timestamp": "2025-12-30 23:52:49"
}
```

## Viewing Logs

### View All Query Expiration Logs
```bash
tail -f storage/logs/laravel.log | grep "Query expiration"
```

### View Only Errors
```bash
tail -f storage/logs/laravel.log | grep "Query expiration" | grep ERROR
```

### View Recent Expiration Checks
```bash
grep "Query expiration check" storage/logs/laravel.log | tail -20
```

### View Specific Query
```bash
grep "query_id\":153" storage/logs/laravel.log
```

### View Today's Logs
```bash
grep "$(date +%Y-%m-%d)" storage/logs/laravel.log | grep "Query expiration"
```

## Log Analysis

### Count Expired Queries Today
```bash
grep "$(date +%Y-%m-%d)" storage/logs/laravel.log | grep "Query expired successfully" | wc -l
```

### Count Failed Expirations
```bash
grep "$(date +%Y-%m-%d)" storage/logs/laravel.log | grep "Failed to expire query" | wc -l
```

### Average Execution Time
```bash
grep "Query expiration check completed" storage/logs/laravel.log | \
  grep "$(date +%Y-%m-%d)" | \
  jq -r '.execution_time_ms' | \
  awk '{sum+=$1; count++} END {print "Average:", sum/count, "ms"}'
```

## Log Retention

Logs are stored in `storage/logs/laravel.log` and follow Laravel's logging configuration. Consider:

1. **Log Rotation**: Configure log rotation for large log files
2. **Separate Log File**: Create a dedicated log file for query expiration
3. **Log Aggregation**: Use tools like Logstash, Fluentd, or CloudWatch for production

## Monitoring Recommendations

1. **Set up alerts** for:
   - Failed query expirations (ERROR level)
   - High execution times (> 10 seconds)
   - Notification failures

2. **Track metrics**:
   - Number of queries expired per day
   - Average execution time
   - Success/failure rate

3. **Regular review**:
   - Weekly review of expiration logs
   - Monthly analysis of trends
   - Quarterly audit of expired queries

## Example Log Flow

```
[INFO] Query expiration check started
[INFO] Query expiration check - queries found (total_expired: 1)
[INFO] Query expiration check - expired queries details (count: 1)
[INFO] Processing expired query (query_id: 153)
[INFO] Query expiration notification sent (query_id: 153)
[INFO] Query expired successfully (query_id: 153, execution_time_ms: 6190.25)
[INFO] Query expiration check completed (successfully_expired: 1, failed: 0)
```

