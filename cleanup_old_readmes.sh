#!/bin/bash
# Script to remove old README files that have been consolidated

echo "🧹 Cleaning up old README files..."

# List of files to delete (they're now in DOCUMENTATION.md)
FILES_TO_DELETE=(
    "100_PERCENT_COMPLETE.md"
    "BACKEND_COMPLETION_SUMMARY.md"
    "COMPLETE_FEATURE_STATUS.md"
    "FEATURE_CHECKLIST.md"
    "FINAL_STATUS.md"
    "FRONTEND_INTEGRATION_SUMMARY.md"
    "HOW_TO_LOGIN.md"
    "LAUNCH_APP.md"
    "LAUNCH_COMPLETE.md"
    "PROJECT_STATUS.md"
    "READY_TO_TEST.md"
    "TESTING_GUIDE.md"
    "CLEANUP_README.md"
)

for file in "${FILES_TO_DELETE[@]}"; do
    if [ -f "$file" ]; then
        echo "  Deleting: $file"
        rm "$file"
    fi
done

echo "✅ Cleanup complete!"
echo ""
echo "📚 Main documentation files:"
echo "  - README.md (Quick start)"
echo "  - DOCUMENTATION.md (Complete docs)"
echo "  - SYSTEM_SPECIFICATION.md"
echo "  - DATABASE_SCHEMA.md"
echo "  - API_SPECIFICATION.md"
echo "  - LARAVEL_SETUP.md"

