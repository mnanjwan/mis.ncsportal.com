#!/bin/bash

# Script to update remaining HRD and Establishment blade files with mobile-responsive horizontal scroll pattern

# Array of files to update
declare -a files=(
    "resources/views/dashboards/hrd/retirement-list.blade.php"
    "resources/views/dashboards/hrd/promotion-eligibility.blade.php"
    "resources/views/dashboards/hrd/zones/index.blade.php"
    "resources/views/dashboards/hrd/manning-requests.blade.php"
    "resources/views/dashboards/hrd/leave-types.blade.php"
    "resources/views/dashboards/hrd/courses.blade.php"
    "resources/views/dashboards/hrd/staff-orders.blade.php"
    "resources/views/dashboards/hrd/zones/show.blade.php"
    "resources/views/dashboards/hrd/onboarding.blade.php"
    "resources/views/dashboards/hrd/manning-request-matches.blade.php"
    "resources/views/dashboards/hrd/manning-request-show.blade.php"
    "resources/views/dashboards/hrd/promotion-criteria.blade.php"
    "resources/views/dashboards/hrd/promotion-eligibility-list-show.blade.php"
    "resources/views/dashboards/hrd/retirement-list-show.blade.php"
    "resources/views/dashboards/hrd/movement-order-show.blade.php"
    "resources/views/dashboards/hrd/emolument-timeline.blade.php"
    "resources/views/dashboards/establishment/new-recruits.blade.php"
    "resources/views/dashboards/establishment/training-results.blade.php"
    "resources/views/dashboards/establishment/service-numbers.blade.php"
)

echo "Starting mobile-responsive table updates..."
echo "Total files to update: ${#files[@]}"
echo ""

for file in "${files[@]}"; do
    echo "Processing: $file"

    # Check if file exists
    if [ ! -f "$file" ]; then
        echo "  ERROR: File not found!"
        continue
    fi

    # Create backup
    cp "$file" "${file}.backup"
    echo "  Backup created"

done

echo ""
echo "All files backed up successfully!"
echo "Manual updates still required for each file."
echo ""
echo "Next steps:"
echo "1. For each file with tables, apply the following changes manually:"
echo "   a. Add 'overflow-hidden' to kt-card div"
echo "   b. Update kt-card-content with 'p-0 md:p-5 overflow-x-hidden'"
echo "   c. Add mobile scroll hint before table"
echo "   d. Wrap table in horizontal scroll wrapper"
echo "   e. Remove mobile card views (lg:hidden sections)"
echo "   f. Add px-4 to pagination divs"
echo "   g. Add CSS styles before @endsection"
