#!/bin/bash
# Consolidate all README files into one

echo "📚 Consolidating README files..."

# Create a consolidated documentation file
cat > DOCUMENTATION.md << 'DOCEOF'
# NCS Employee Portal - Complete Documentation

This document consolidates all project documentation.

## Table of Contents
1. [System Specification](#system-specification)
2. [Database Schema](#database-schema)
3. [API Specification](#api-specification)
4. [Laravel Setup](#laravel-setup)
5. [Project Status](#project-status)

---

DOCEOF

# Append each documentation file
echo "## System Specification" >> DOCUMENTATION.md
echo "" >> DOCUMENTATION.md
cat SYSTEM_SPECIFICATION.md >> DOCUMENTATION.md
echo "" >> DOCUMENTATION.md
echo "---" >> DOCUMENTATION.md
echo "" >> DOCUMENTATION.md

echo "## Database Schema" >> DOCUMENTATION.md
echo "" >> DOCUMENTATION.md
cat DATABASE_SCHEMA.md >> DOCUMENTATION.md
echo "" >> DOCUMENTATION.md
echo "---" >> DOCUMENTATION.md
echo "" >> DOCUMENTATION.md

echo "## API Specification" >> DOCUMENTATION.md
echo "" >> DOCUMENTATION.md
cat API_SPECIFICATION.md >> DOCUMENTATION.md
echo "" >> DOCUMENTATION.md
echo "---" >> DOCUMENTATION.md
echo "" >> DOCUMENTATION.md

echo "## Laravel Setup" >> DOCUMENTATION.md
echo "" >> DOCUMENTATION.md
cat LARAVEL_SETUP.md >> DOCUMENTATION.md

echo "✅ Documentation consolidated into DOCUMENTATION.md"
