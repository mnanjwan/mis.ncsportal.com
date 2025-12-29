# NCS Employee Portal - System Specification

## Table of Contents
1. [Roles & Permissions](#roles--permissions)
2. [Workflows](#workflows)
3. [Application Flows Based on Role Interactions](#application-flows-based-on-role-interactions)
4. [Critical Interaction Points](#critical-interaction-points)
5. [Leave Types](#leave-types)
6. [Business Rules & Constraints](#business-rules--constraints)
7. [Key Optimizations Implemented](#key-optimizations-implemented)
8. [System Safeguards](#system-safeguards)
9. [Performance Indicators](#performance-indicators)

---

## Roles & Permissions

### HRD (Human Resources Department) - System Administrator
**Primary Role:** Overall system management and strategic HR operations

**Access Level:** Full system access across all commands

**Core Functions:**
1. Generate Staff Orders
2. Generate Movement Orders
3. Onboard Serving Officers
4. Generate Eligibility List for Promotion
5. Generate Retirement List
6. Nominate Officers for courses and indicate completion of the course which goes directly into the officers record
7. Create timeline for Officers to raise emolument (The Start and End date can be extended by HRD, Cron Job)
8. Trigger the system to match criteria for Manning Level requests
9. Alter Staff Orders and Movement Orders
10. Set the number of years that an officer will stay on the rank to be eligible for promotion
11. Create new types of leave and assign duration
12. Onboard officers for NCS Employee App
13. Create, extend, and manage emolument timelines with automated cron job capabilities
14. Generate and process staff orders for officer postings
15. Create movement orders based on tenure criteria or manning requirements
16. Override posting decisions when necessary
17. System-wide configuration and parameter management
18. Generate comprehensive system reports
19. Role Assignments Management: Assign roles to officers for specific commands via "Role Assignments Management -> Assign Role to Officer" (including Admin, Investigation Unit, and other roles)
20. View accepted queries in officer profiles (queries accepted by Staff Officers become visible in HRD's view of officer profiles)
21. View all accepted queries system-wide through dedicated Query Management section
22. Receive in-app notifications when queries are accepted (no email notifications for HRD)
23. Filter queries by command and officer for comprehensive disciplinary record review

### Admin - Command Role Assignment Manager
**Primary Role:** Command-specific role assignment and user management

**Access Level:** Role assignments specific to assigned command only

**Creation:** Admin users are assigned by HRD through Role Assignments Management -> Assign Role to Officer, selecting an officer and assigning them the Admin role for a specific command

**Core Functions (Specific to Assigned Command):**
1. Role Assignments Staff Officer (within assigned command)
2. Role Assignments Area Comptroller or Unit Head (within assigned command)
3. Role Assignments DC Admin (within assigned command)
4. Manage role assignments within their assigned command only
5. Assign administrative roles to officers in their assigned command
6. Update role assignments as needed within command scope
7. View current role assignments in their assigned command
8. Maintain role assignment records and audit trail for their command

**Note:** Investigation Unit role assignments are managed exclusively by HRD, not by Admin role assignment managers.

### Staff Officer - Administrative Manager
**Primary Role:** Command-level administration and coordination

**Access Level:** Command-level operations

**Core Functions:**
1. Prepares the Roaster
2. Sends the Manning Level of officers to HRD (Rank, compulsory, Sex and Qualification optional)
3. Minutes pass request to the Comptroller or DC Admin
4. Prepare Internal Staff Order
5. Prepare a release letter
6. Documents an officer
7. Sends Manning Level Request to HRD
8. Minutes Leave or Pass requests to DC Admin for approval
9. Prints out approved leave or pass
10. Can add officers to Management Groups in NCS Employee App (for unit heads below Chief Superintendent and Legal department non-officers)
11. Prepare and manage duty rosters for the command
12. Create manning level requests (specify rank, sex, qualification requirements)
13. Process leave and pass applications from officers
14. Maintain command nominal roll
15. Manage command chat groups in NCS App
16. Track officer movements in and out of command
17. Coordinate with other unit staff officers
18. Generate command-level reports and statistics
19. Issue queries to officers within command with written reasons
20. Review officer responses to queries
21. Accept or reject query responses (Accepted queries become part of officer's disciplinary record)
22. Manage query workflow and tracking

### Building Unit - Accommodation Manager
**Primary Role:** Officer quarters allocation and management

**Access Level:** Command-level quarters and accommodation records (also applies to Headquarters as Building Unit is a unit under HQ)

**Core Functions:**

**Quarter Creation & Management:**
1. Create quarters (Block Numbers) with unique identifiers
2. Assign quarter types (Single Room, One Bedroom, Two Bedroom, Three Bedroom, Four Bedroom, Duplex, Bungalow, Other)
3. Link quarters to specific commands
4. Maintain quarters occupancy database
5. Track quarters maintenance requirements
6. View quarters statistics (Total, Occupied, Available)

**Officer Search & Allocation:**
7. Search for officers within assigned command by service number, name, or email
8. Allocate quarters (rooms) to eligible officers at command level
9. Set allocation dates
10. Deallocate quarters when officers are posted or transferred

**Quarter Request Processing:**
11. View quarter requests from officers in assigned command
12. Process quarter allocation requests
13. Approve quarter requests (allocates quarter and updates officer's quartered status)
14. Reject quarter requests with reason (one-time rejection only - cannot reject again after first rejection)
15. Track request status (PENDING, APPROVED, REJECTED)

**Quartered Status Management:**
16. At Command level, enter Quartered status Yes or No option on dropdown
17. Update individual officer quartered status (Yes/No)
18. Bulk update quartered status for multiple officers
19. Status automatically reflects on PE (Personal Emolument) form - when quartered status is Yes, it shows Yes on emolument form

**Reporting & Lists:**
20. Generate accommodation reports
21. Manage waiting lists for quarters
22. View allocation history
23. Track quarters occupancy trends

### Establishment - Service Number Administrator
**Primary Role:** New officer registration and service number management

**Access Level:** New recruitment and service numbers

**Core Functions:**
1. Assign New Appointment Numbers to new recruits with automatic prefix assignment:
   - **CDT prefix** for ASC II GL 08 and above, AIC, DSC ranks
   - **RCT prefix** for IC GL 07 and below ranks
   - Prefix automatically determined based on rank and GL level
   - Sequential numbering per prefix (e.g., CDT00001, CDT00002, RCT00001, RCT00002)
2. Receive sorted training results from TRADOC (grouped by rank)
3. Allocate Service Numbers grouped by rank:
   - Each rank maintains its own service number sequence
   - Within each rank, service numbers assigned based on training performance (highest to lowest)
   - System automatically tracks last service number per rank
   - Service numbers continue from last number for that specific rank
4. Maintain service number registry per rank
5. Process new recruit documentation
6. Create initial officer records
7. Coordinate recruitment exercises
8. Process appointment letters
9. Assign initial ranks to new officers
10. Ensure unique identification for all officers
11. Generate service number assignment reports by rank
12. View rank-based assignment previews before processing

### Accounts - Financial Processor
**Primary Role:** Payment processing and financial management

**Access Level:** Financial records system-wide

**Core Functions:**
1. Generate list of officers that has been Validated (Remember Validation is done by the Area Controller). The fields needed by accounts are:
   - Bank
   - Account Number
   - PFA (Pension Fund Administrator)
   - Retirement Savings Account (RSA)
2. Generate list of deceased officers (This list is from Welfare Unit)
3. Generate list of interdicted officers (Officers placed on interdiction by Investigation Unit)
4. Extract payment data: Bank Name, Account Number, PFA, RSA
5. Process salary payments and remittances
6. Calculate and process death benefits
7. Process pension remittances
8. Generate payment reports and reconciliations
9. Maintain financial audit trails
10. Handle payment exceptions and corrections
11. Generate payroll and financial statistics
12. Verify and approve account number changes submitted by officers
13. Verify and approve RSA PIN number changes submitted by officers (RSA PIN has the prefix PEN)

### Board (Promotion Board) - Career Progression Manager
**Primary Role:** Management of promotions and rank changes

**Access Level:** Promotion and career records

**Core Functions:**
1. Changes the officers Rank to a new rank (This part is not edited by the officer)
2. Review promotion eligibility lists generated by HRD
3. Conduct promotion exercises
4. Update promotion effective dates
5. Maintain promotion history records
6. Generate seniority lists
7. Consider disciplinary records in promotion decisions
8. Make promotion recommendations
9. Track time in rank for all officers

### Assessor - Emolument Reviewer
**Primary Role:** First-level review of emolument submissions

**Access Level:** Subordinate officers only within command

**Core Functions:**
1. Clicks Access for the officer (The only officers an Assessor can see are those officers under his command that have raised Emolument)
2. The officers will appear on a list and they'd be a button for view details
3. View list of subordinate officers who have raised emoluments
4. Access detailed emolument information for review
5. Verify accuracy of banking information
6. Validate PFA and RSA PIN details
7. Confirm completeness of next of kin information
8. Perform assessment by clicking "Assess" for each reviewed emolument
9. Flag discrepancies or issues
10. Generate assessment reports
11. Track assessment completion rates

### Validator - Final Emolument Approver
**Primary Role:** Final verification before payment processing

**Access Level:** Command-level validation

**Core Functions:**
1. Validates officers that the Assessor has assessed
2. Review emoluments that have been assessed
3. Perform final validation checks
4. Ensure regulatory compliance
5. Cross-verify documentation
6. Approve emoluments for accounts processing
7. Quality control of assessed information
8. Maintain validation logs

### Officer - End User
**Primary Role:** Personal record management and service applications

**Access Level:** Personal records only

**Core Functions:**
1. Onboarding: Fill all the required fields
2. Apply for Leave
3. Apply for Pass
4. Raise Emolument (This is done once a year)
5. Chatting through the NCS Employee Application
6. Change their display picture
7. Login to raise emolument during the timeline period set by HRD
8. Edit the following fields during emolument: Bank Name, Account Number, Name of Pension Fund Administrator (PFA), Retirement Savings Account (RSA) PIN (usually with a prefix PEN and usually 12 digits), and their next of KIN
9. Complete onboarding process with all required information
10. Update editable emolument fields: Bank Name, Account Number, PFA, RSA PIN, Next of Kin
11. Apply for leave (maximum 2 times per year for annual leave)
12. Apply for pass (maximum 5 days, only after exhausting annual leave)
13. View personal service records and history
14. Access NCS Employee App for communication
15. Participate in command-specific chat rooms
16. Update profile picture
17. Upload and maintain personal documents (JPEG format)
18. Track application statuses (leave, pass, emolument)
19. Receive system notifications and alerts
20. Send new account number to Accounts Section for verification
21. Send new RSA PIN number to Accounts Section for verification (RSA PIN has the prefix PEN)
22. Add, edit, or delete Next of KIN details (Name, Relationship, Phone Number, Address, Email) for Welfare verification
23. View retirement information including calculated retirement date, retirement type (AGE or SVC), and countdown to retirement
24. Receive and view retirement alerts (3 months before retirement date)
25. Receive queries from Staff Officer
26. Respond to queries issued by Staff Officer
27. View query history and status in personal profile

### Area Controller (Comptroller) - Senior Validator
**Primary Role:** Command oversight and final validation authority

**Access Level:** Area command with validation authority

**Core Functions:**
1. His name will appear on the Leave or pass as the officer that has approved it
2. Approve Roaster generated by the Staff Officer
3. Approves Manning Level to be sent to HRD
4. Validates officers (Validation is required before Accounts can generate lists)
5. Can indicate that an officer is deceased (along with Staff Role)
6. Validate emoluments after assessment
7. Provide final approval for emolument processing
8. Approve duty rosters submitted by Staff Officer
9. Approve manning level requests before forwarding to HRD
10. Indicate when an officer is deceased (initiates benefits process)
11. Monitor overall command performance
12. Make strategic decisions for the command
13. Oversee multiple units within area of responsibility
14. Review and approve internal movements
15. View accepted queries for officers in their command (disciplinary record)
16. Receive email and in-app notifications when queries are accepted in their command
17. Access query details and disciplinary history for command officers

### CGC (Comptroller General of Customs) - Preretirement Leave Authority
**Primary Role:** Strategic oversight and preretirement leave management

**Access Level:** System-wide preretirement leave approval authority

**Core Functions:**
1. Search and view officers approaching preretirement leave (3 months before retirement)
2. Review officers automatically placed on preretirement leave
3. Approve officers to continue working during preretirement period (preretirement leave "in office")
4. Manage exceptions to automatic preretirement leave placement
5. View comprehensive preretirement leave reports and statistics
6. Monitor officers working during preretirement period
7. Strategic decision-making for critical officer retention during preretirement
8. Override automatic preretirement leave placement when necessary
9. Track and audit preretirement leave approvals
10. Generate preretirement leave management reports

**Preretirement Leave Management:**
- Officers are automatically placed on preretirement leave 3 months before retirement
- Only CGC can approve officers to work during the last 3 months before retirement
- Officers cannot apply for preretirement leave - it is automatic and CGC-managed
- CGC has exclusive authority to approve "preretirement leave in office" status

### DC Admin (Deputy Comptroller Administration) - Operational Approver
**Primary Role:** Day-to-day approval of routine administrative requests

**Access Level:** Command-level approval authority

**Core Functions:**
1. Approve Pass
2. Approve Leave
(Both are sent to him by the staff officer after the officer has applied)
3. Review and approve/reject leave applications minuted by Staff Officer
4. Review and approve/reject pass requests (ensure 5-day maximum)
5. Process urgent leave requests
6. Ensure compliance with leave and pass eligibility rules
7. Support Area Controller in administrative functions
8. Handle routine operational approvals
9. Maintain approval audit trail
10. View accepted queries for officers in their command (disciplinary record)
11. Receive email and in-app notifications when queries are accepted in their command
12. Access query details and disciplinary history for command officers
13. Approve duty rosters submitted by Staff Officer (alongside Area Controller)
14. View pending duty rosters on dashboard for quick actions
15. Review duty roster details including OIC, 2IC, and all assignments

### Welfare - Benefits Administrator
**Primary Role:** Deceased officer management and welfare support

**Access Level:** Welfare and benefits records

**Core Functions:**
1. Validates when an officer is deceased (The Area Controller or Staff Role can indicate that an officer is deceased)
2. Generates data on the deceased officer with the following fields:
   - SVC no
   - Rank
   - DOB
   - Next of Kin(s)
   - Bank Name
   - Account Number
   - Retirement Savings Account Administrator (RSA), e.g., Tangerine, Stanbic IBTC Pensions etc
3. Validate deceased officer status upon report
4. Generate comprehensive deceased officer data including all required fields
5. Process welfare claims and benefits
6. Coordinate with Accounts for benefit payments
7. Maintain beneficiary records
8. Handle compassionate cases
9. Process emergency support requests
10. Generate welfare reports
11. Verify and approve Next of KIN changes submitted by officers (Name, Relationship, Phone Number, Address, Email)

### TRADOC - Training Command
**Primary Role:** Training administration and results management

**Access Level:** Training records and results

**Core Functions:**
1. Upload training results in CSV format
2. View and manage training results
3. Sort results by performance (highest to lowest)
4. Submit sorted results to Establishment unit
5. Track training completion status
6. Generate training reports
7. Manage training batches and cohorts

### ICT - Information and Communication Technology
**Primary Role:** Email and system account management

**Access Level:** Email management and account administration

**Core Functions:**
1. Create new email addresses on customs.gov.ng domain
2. Delete former personal email addresses
3. Manage officer email accounts
4. Assign email addresses based on service numbers
5. Track email creation and deletion status
6. Generate email account reports
7. Manage system user accounts

### TRADOC - Training Command
**Primary Role:** Training results management and performance tracking

**Access Level:** Training results and officer performance data

**Core Functions:**
1. Upload training results in CSV format
2. View and manage training results
3. Sort officers by performance (highest to lowest)
4. Generate training performance reports
5. Track officer training completion status
6. Export training results for Establishment processing

### ICT - Information and Communication Technology
**Primary Role:** Email account management and IT infrastructure

**Access Level:** Email account management and officer IT accounts

**Core Functions:**
1. Create new email addresses on customs.gov.ng domain
2. Delete former personal email addresses
3. Manage officer email account lifecycle
4. Track email account status
5. Generate email account reports
6. Coordinate email creation with service number assignment

### Investigation Unit - Disciplinary Investigation Manager
**Primary Role:** Officer investigation and disciplinary status management

**Access Level:** System-wide officer investigation and disciplinary actions

**Core Functions:**
1. Search any officer system-wide by service number, name, or email
2. Send investigation invitation message to officers (email and in-app notification)
3. Place officers on investigation status:
   - **Ongoing Investigation** - Officer is under investigation
   - **Interdiction** - Officer is interdicted (prevents promotion eligibility)
   - **Suspension** - Officer is suspended (prevents promotion eligibility)
4. View investigation history and status for all officers
5. Track investigation dates and outcomes
6. Manage investigation records and documentation
7. Update investigation status as cases progress
8. Remove investigation status when cases are resolved

**Investigation Status Impact:**
- Officers with **Ongoing Investigation**, **Interdiction**, or **Suspension** status cannot appear on the Promotion Eligibility List
- Officers with **Interdiction** status appear on Accounts unit's interdicted officers list
- Investigation status affects officer's eligibility for promotions and other career progression activities

**Notification System:**
- Officers receive email and in-app notifications when investigation invitation is sent
- Officers receive notifications when investigation status changes
- System automatically updates promotion eligibility lists when investigation status changes

### Zone Coordinator - Zonal Posting Manager
**Primary Role:** Zonal-level officer posting management

**Access Level:** Zone-level posting operations

**Core Functions:**
1. Post officers of GL 07 and below within their assigned zone
2. View officers within their zone
3. Generate zonal staff orders for eligible officers
4. Cannot post officers above GL 07 (HRD exclusive)
5. Cannot post officers outside their zone
6. Coordinate with HRD for cross-zone postings
7. Manage postings between commands within the same zone
8. View posting history for their zone

**Posting Restrictions:**
- Only officers with Grade Level 07 and below can be posted by Zone Coordinators
- Postings must be within the same zone (both from and to commands must be in coordinator's zone)
- Officers above GL 07 require HRD approval for posting
- Zone Coordinators cannot create movement orders (HRD exclusive)

**Zone Structure:**
The system is organized into the following zones:

1. **HEADQUARTERS**
   - Commands: CGC OFFICE, FATS-HQTRS, SR&P-HQTRS, HRD-HQTRS, E I & I-HQTRS, EXCISE & FTZ-HQTRS, TRADOC, LEGAL UNIT, ICT-MOD-HQTRS

2. **Zone A HQ**
   - Commands: APAPA, TCIP, MMIA, MMAC, KLTC, LAGOS INDUSTRIAL, SEME, OGUN I, OGUN II, OYO-OSUN, ONDO EKITI, PTML, PCA Zone A, LEKKI FREE TRADE ZONE, LILYPOND EXPORT COMMAND, WMC, IKORODU, FOU A

3. **Zone B HQTRS**
   - Commands: KADUNA, KANO JIGAWA, SOKOTO ZAMFARA, NIGER KOGI, FCT, KWARA, KEBBI, NWM, PT NA BE, PCA Zone B, FOU B

4. **Zone C**
   - Commands: AN EB EN, IMO ABIA, PH I BAYELSA, PH II ONNE, EDO DELTA, CR AK, EMC, PCA Zone C, FOU C

5. **Zone D**
   - Commands: BA GM, AD TR, BN YB, FOU D, PCA Zone D

**HRD Zone & Command Management:**
- HRD can create and manage zones
- HRD can create and manage commands (must assign to a zone)
- All commands belong to a zone
- All users belong to a command (therefore have a zone)
- Zone Coordinators are assigned to a command within their zone

---

## Workflows

### 1. Emolument Form Workflow

**Timeline Creation Phase:**
1. **HRD creates timeline** - HRD will create a timeline for Officers to raise emolument (The Start and End date can be extended by HRD, Cron Job)
2. **System activates timeline** - System activates timeline and notifies all officers
3. **Cron job monitors** - Cron job monitors for automatic extensions if configured

**Officer Submission Phase:**
1. **Officers login** - Officers can Login at the point to be able to raise emolument
2. **Officers view previous data** - Officers view previous year's data for reference
3. **Officers edit fields** - The Editable Fields are:
   - Bank Name
   - Account Number
   - Name of Pension Fund Administrator (PFA)
   - Retirement Savings Account (RSA) PIN (usually with a prefix PEN and usually 12 digits)
   - Next of KIN
4. **Officers submit** - Officers submit emolument (Status: RAISED)

**Assessment Phase:**
1. **Assessor receives notification** - Assessor receives notification of raised emoluments
2. **Assessor accesses** - Assessor clicks Access for the officer (The only officers an Assessor can see are those officers under his command that have raised Emolument). The officers will appear on a list with a button for view details
3. **Assessor reviews** - Assessor reviews only subordinate officers' submissions
4. **Assessor verifies** - Assessor verifies all information accuracy
5. **Assessor approves** - Assessor approves assessment (Status: ASSESSED)

**Validation Phase:**
1. **Validator receives** - Validator/Area Controller receives assessed emoluments
2. **Validator validates** - Validator validates officers that the Assessor has assessed
3. **Validator performs final review** - Validator performs final review and compliance check
4. **Area Controller validates** - Area Controller validates the officers (This validation is required before Accounts can generate lists)
5. **Validator approves** - Validator approves for payment (Status: VALIDATED)

**Payment Phase:**
1. **Accounts generates list** - Accounts generates list of officers that has been Validated with the fields: Bank, Account Number, PFA (Pension Fund Administrator), and Retirement Savings Account (RSA)
2. **Accounts extracts data** - Accounts extracts payment information
3. **Accounts processes** - Accounts processes payments (Status: PROCESSED)

### 2. Officers Onboarding Workflow

**For New Recruits:**
1. **Establishment creates recruit** - Establishment creates new recruit record with rank and GL level
2. **Automatic appointment number assignment** - System automatically assigns appointment number with prefix based on rank:
   - **CDT prefix** for ASC II GL 08+, DSC ranks (e.g., CDT00001, CDT00002)
   - **RCT prefix** for IC GL 07-, AIC ranks (e.g., RCT00001, RCT00002)
   - Sequential numbering per prefix
   - Used throughout training period
3. **System generates link** - System generates onboarding link
4. **Email sent** - An email is sent to the officer for onboarding (At this point the Officer uses his personal email)
5. **Officer receives email** - Officer receives email and clicks link
6. **Officer fills information** - Officers will fill the following information in multi-step form:
   - Step 1: Personal Information
   - Step 2: Employment Details
   - Step 3: Banking and Pension
   - Step 4: Next of Kin and Documents
   
   **Complete Field List:**
   - Service Number (assigned after training)
   - Initials
   - Surname
   - Sex
   - Date of First Appointment
   - Date of Present Appointment
   - Substantive Rank
   - Salary Grade Level
   - Date of Birth
   - State of Origin
   - LGA
   - Geopolitical Zone
   - Marital Status
   - Entry Qualification
   - Discipline (Optional for WAEC, NECO and Below)
   - Additional Qualification (Optional)
   - Present Station
   - Date posted to station
   - Residential Address
   - Permanent Home Address
   - Phone Number
   - Email
   - Bank
   - Bank Account Number
   - Sort Code (Optional)
   - Name of PFA
   - RSA Number
   - Unit
   - Name(s) of Next of KIN
   - Relationship
   - Interdicted
   - Suspended
   - Quartered
7. **Officer accepts disclaimer** - Officer accepts disclaimer about false information with caveat: "Any false information provided by you shall be subject to severe disciplinary actions which may include and not be limited to dismissal"
8. **Upload documents** - Officers must upload credentials and documents preferably in JPEG to save space
9. **Caveat displayed** - System displays caveat: "Any false information provided by you can lead to Dismissal for Forgery under the PSR Rules"
10. **Training Phase** - Officers undergo training using their appointment numbers (CDT/RCT prefixes)
11. **TRADOC uploads results** - After training, TRADOC uploads officers results in CSV format with substantive rank included, sorted by performance (highest to lowest)
12. **System groups by rank** - System automatically groups training results by officer's substantive rank
13. **Establishment receives grouped list** - Establishment receives training results grouped by rank, sorted by performance within each rank
14. **Rank-based service number assignment** - System assigns service numbers grouped by rank:
    - Each rank maintains its own service number sequence
    - Within each rank, service numbers assigned based on performance (highest to lowest)
    - System automatically tracks last service number per rank
    - Service numbers continue from last number for that specific rank
15. **ICT creates email** - ICT will create new email addresses for them on the customs.gov.ng domain
16. **ICT deletes personal email** - ICT deletes the former personal email address
17. **System creates account** - System creates account and assigns to command chat

**For Existing Officers:**
1. **HRD initiates** - HRD initiates onboarding
2. **HRD enters email** - HRD enters officer email
3. **System sends link** - System sends unique onboarding link
4. **Officer receives email** - Officer receives email and clicks link
5. **Officer fills information** - Officers will fill the following information in multi-step form:

### 3. Pass and Leave Workflow

**Important Rules:**
- Maximum number of days given to an officer for pass should be 5 days
- Pass can only be applied if and only if the officer has exhausted the Annual leave
- Annual leave can be applied for a maximum of 2 times
- Annual leave duration: 28 Days for GL 07 and Below, 30 days for Level 08 and above

**Leave Application Process:**

**Application Phase:**
1. **Officer checks eligibility** - Officer checks leave eligibility in system
2. **Officer applies** - Officer applies for Leave or Pass
3. **Officer selects type** - Officer selects from 28 leave types
4. **Officer submits** - Officer submits application with dates and reason
5. **System validates** - System calculates days and validates eligibility

**Processing Phase:**
1. **Staff Officer receives** - Staff Officer receives leave application
2. **Staff Officer reviews** - Staff Officer reviews supporting documents
3. **Staff Officer minutes** - Staff Officer Minutes it to the DC Admin for approval
4. **Status changes** - Application status changes to MINUTED

**Approval Phase:**
1. **DC Admin reviews** - DC Admin reviews minuted application
2. **DC Admin approves** - DC Admin approves or rejects with reason
3. **Area Controller name appears** - If approved, Area Controller's name appears on document
4. **Status changes** - Status changes to APPROVED

**Distribution Phase:**
1. **Staff Officer prints** - Staff Officer prints approved leave document
2. **Staff Officer distributes** - Staff Officer distributes to officer
3. **Officer receives notification** - System sends automatic notification to officer
4. **System monitors** - System monitors leave (alerts 72 hours before end)

**Pass Application Process (Special Rules):**
- Maximum 5 days only
- Annual leave must be exhausted
- Maximum 2 passes per year

**Process Flow:**
1. **Officer applies** - Officer applies for pass (system validates eligibility)
2. **Staff Officer processes** - Staff Officer processes and minutes to DC Admin
3. **DC Admin approves** - DC Admin approves (ensures 5-day limit)
4. **Staff Officer prints** - Staff Officer prints and distributes
5. **System monitors** - System monitors pass duration
6. **System alerts** - Automatic alert sent on expiry date

### 4. Manning Level Workflow

**Request Creation:**
1. **Staff Officer analyzes** - Staff Officer analyzes command needs
2. **Staff Officer prepares list** - The Staff Officer will prepare a list of officers that the command needs (Rank, Sex and Qualification optional)
3. **Staff Officer enters requirements** - The staff officer will enter for example in the Manning Request: DC needed 3, AC needed 3, etc
4. **Staff Officer submits** - Staff Officer submits to Area Controller

**Approval Process:**
1. **Staff Officer minutes** - The Staff Officer Minutes it for the Comptroller's approval
2. **Area Controller reviews** - Area Controller reviews justification
3. **Area Controller approves** - Area Controller approves and forwards to HRD

**Matching Process:**
1. **HRD receives request** - Once the Comptroller Approves, it goes to HRD
2. **System searches** - System searches for matching officers
3. **System filters** - System filters by rank, sex, qualification, status
4. **HRD triggers matching** - Then HRD will trigger the system to match the criteria
5. **HRD selects** - HRD selects from matched candidates
6. **HRD generates orders** - HRD generates movement orders
7. **HRD can alter** - HRD must have an option to be able to alter the Staff Orders and Movement Orders

**Implementation:**
1. **Officers notified** - Officers receive posting notifications
2. **New command sees officer** - New command Staff Officer sees officer on dashboard
3. **Staff Officer documents** - Staff Officer documents arrival
4. **System updates** - System updates nominal rolls and chat rooms

### 5. Staff Order and Movement Order Workflow

**Staff Order:**
1. **HRD searches officer** - HRD will search for the officer and change the posting at any time he deems fit
2. **Officer appears on dashboard** - The officer posted appears on the dashboard of the Staff Officer of the new officer's posting
3. **Staff Officer documents** - The Staff Officer documents the officer
4. **Officer removed from previous roll** - The officer's name automatically goes off the previous command's nominal roll
5. **Chat room transfer** - When an officer is posted out, he goes to his new command chat room and leaves the previous one

**Movement Order:**
1. **HRD enters criteria** - HRD will enter a criteria that will bring up officers that have spent a particular time it deems fit (this timing will be in Months)
2. **HRD posts officers** - HRD will post the officers
3. **HRD can use Manning Level** - HRD can also use Manning Level for postings
4. **Officer appears on dashboard** - The officer posted appears on the dashboard of the Staff Officer of the new officer's posting
5. **Staff Officer documents** - The Staff Officer documents the officer
6. **Officer removed from previous roll** - The officer's name automatically goes off the previous command's nominal roll
7. **Chat room transfer** - When an officer is posted out, he goes to his new command chat room and leaves the previous one

**Note:** This workflow can occur after the manning request or without the manning request.

### 6. Building/Quartering Workflow

**Quarter Creation:**
1. **Building Unit creates quarters** - Building Unit creates Block Numbers (quarters) with unique identifiers and types
2. **Quarters linked to commands** - Each quarter is assigned to a specific command

**Quarter Request Process:**
3. **Officer requests quarter** - Officer can request a quarter through the system (status: PENDING)
4. **Building Unit reviews** - Building Unit views pending requests for their command
5. **Building Unit approves** - Building Unit can approve request and allocate quarter (status: APPROVED)
6. **Building Unit rejects** - Building Unit can reject request with reason (one-time only - cannot reject again after first rejection) (status: REJECTED)
7. **Status updates** - When approved, officer's quartered status automatically updates to Yes

**Quartered Status Management:**
8. **Command level entry** - At the Command level, Building Unit enters the Quartered status Yes or No option on the dropdown
9. **Status on PE form** - The status on the PE (Personal Emolument) form indicates whether the Officer is quartered or not
10. **Automatic sync** - When Building Unit allocates a quarter or updates quartered status to Yes, it automatically reflects on the officer's emolument form

**Direct Allocation (Without Request):**
11. **Search officers** - Building Unit can search for officers at command level
12. **Direct allocation** - Building Unit can directly allocate quarters to officers without waiting for request
13. **Status update** - Direct allocation automatically updates officer's quartered status to Yes

**Deallocation:**
14. **Deallocate on posting** - When officers are posted, Building Unit deallocates their quarters
15. **Status update** - Deallocation automatically updates officer's quartered status to No

**Note:** Building Unit functionality applies to both regular commands and Headquarters, as Building Unit is also a unit under HQ.

### 7. Eligibility List for Promotion Workflow

**Setup Phase:**
1. **HRD sets criteria** - The HRD will set the number of years that an officer will stay on the rank to be eligible for promotion
2. **System stores criteria** - System stores promotion criteria

**Generation Phase:**
1. **System checks eligibility** - System automatically checks eligibility
2. **System checks exclusions** - The system will check if the officer has been:
   - Officers not meeting time in rank
   - Interdicted (set by Investigation Unit)
   - Suspended (set by Investigation Unit)
   - Dismissed (set by Discipline seat)
   - Officers under investigation (ongoing_investigation set by Investigation Unit)
   - Has a pending investigation issue (set by Investigation Unit)
   - Deceased (set by Welfare seat)
3. **Excluded officers** - The above category of officers won't feature on the Eligibility List even if they are due for promotion
4. **System generates list** - System generates eligibility list

**Board Review:**
1. **Board receives list** - Board receives eligibility list
2. **Board conducts exercise** - Board conducts promotion exercise
3. **Board makes decisions** - Board makes promotion decisions
4. **Board updates ranks** - Board updates ranks in system

**Implementation:**
1. **System updates records** - System updates officer records
2. **New rank recorded** - New rank and date recorded
3. **Officers notified** - Officers receive promotion notifications

**List Fields:**
The Eligibility List carries the following fields:
   - S no
   - Initials
   - Rank
   - Initial
   - State
   - Date of birth
   - Date of first appointment
   - Date of present appointment (Date of present appointment is the date of last promotion, of course supplied by the board)

### 8. Statutory Retirement List Workflow

**Retirement Criteria:**
- An officer is due for retirement when he reaches the age of 60 years or 35 years in service, whichever comes first

**Automatic Detection:**
1. **System daily checks** - System daily checks for:
   - Officers reaching age 60
   - Officers completing 35 years service
2. **System generates list** - System generates retirement list

**Retirement Alert System:**
1. **System calculates retirement date** - For each officer, system calculates:
   - Age-based retirement: Date of Birth + 60 years
   - Service-based retirement: Date of First Appointment + 35 years
   - Actual retirement date: Whichever comes earlier (AGE or SVC)
2. **3-Month Alert** - System alerts the officer 3 months before retirement date:
   - Alert notification sent to officer
   - Officer dashboard displays retirement countdown
   - Retirement information visible to officer
   - System tracks alert sent status

**Processing:**
1. **HRD reviews** - HRD reviews retirement list
2. **HRD generates list** - The list is generated by HRD and contains the following fields:
   - S no
   - Rank
   - Initials
   - Name
   - Condition for retirement (AGE or SVC)
   - Date of Birth (DOB)
   - Date of First Appointment (DOFA)
   - Date of Pre Retirement Leave (DOPR) - this pre Retirement is 3 months before retirement date
   - Retirement Date
3. **System calculates** - System calculates pre-retirement leave (3 months prior)
4. **Automatic Preretirement Leave Placement:**
   - When an officer reaches 3 months before retirement date, the system automatically:
     - Sends notification to the officer about preretirement leave
     - Places the officer on preretirement leave automatically
     - Updates officer status to "ON_PRERETIREMENT_LEAVE"
     - Records the automatic placement date
5. **CGC Approval Process (Exception):**
   - CGC can search for officers approaching preretirement leave
   - CGC can approve officers to continue working during preretirement period
   - When CGC approves "preretirement leave in office":
     - Officer status changes to "PRERETIREMENT_LEAVE_IN_OFFICE"
     - Officer can continue working for the last 3 months before retirement
     - CGC approval is recorded with timestamp and approval details
   - Only CGC has authority to approve this exception
6. **Officer Restrictions:**
   - Officers CANNOT apply for preretirement leave
   - Preretirement leave is strictly automatic and CGC-managed
   - Officers can only view their preretirement leave status
7. **Notifications sent** - Notifications sent to:
   - Retiring officer (3 months before retirement - automatic placement notification)
   - CGC (when officer is automatically placed on preretirement leave)
   - Accounts (for benefits processing)
   - Welfare (for transition support)
8. **Officer informed** - The officer will be automatically informed when placed on preretirement leave

### 9. Service Number Allocation Workflow

**Appointment Number Assignment (Training Phase):**
1. **Establishment creates new recruits** - Establishment creates new officer records with rank and GL level
2. **Automatic prefix assignment** - System automatically assigns appointment number prefix based on rank and GL level:
   - **CDT prefix** for:
     - ASC II GL 08 and above
     - DSC ranks
   - **RCT prefix** for:
     - IC GL 07 and below
     - AIC ranks
3. **Sequential numbering per prefix** - Each prefix maintains its own sequence:
   - IC: RCT00001, RCT00002, RCT00003...
   - ASC II: CDT00003, CDT00004...
   - AIC: RCT00005, RCT00006...
   - DSC: CDT00007, CDT00008...
4. **Used during training** - These appointment numbers are used throughout training period

**Service Number Assignment (Post-Training - Rank-Based):**
1. **TRADOC uploads results** - After training, TRADOC uploads results in CSV format with officer's substantive rank included
2. **System groups by rank** - System automatically groups training results by officer's substantive rank
3. **Establishment receives grouped list** - Establishment receives training results grouped by rank, sorted by performance within each rank
4. **Rank-based assignment** - System assigns service numbers grouped by rank:
   - Each rank maintains its own service number sequence
   - System tracks last service number per rank automatically
   - Within each rank, service numbers assigned based on performance (highest to lowest)
   - Example: If IC rank last service number is NCS65000, next IC officer gets NCS65001
   - Example: If ASC II rank last service number is NCS65200, next ASC II officer gets NCS65201
5. **Performance-based within rank** - Within each rank:
   - Highest scorer in that rank gets first available number for that rank
   - Next scorer in same rank gets next number for that rank
   - Different ranks have independent sequences
6. **CSV upload by rank** - TRADOC uploads results grouped by rank, ensuring proper rank-based assignment

**For Existing Officers (Sequential):**
1. **Establishment allocates** - This is done by establishment unit
2. **Sequential numbering** - Any new officer documented will start from the last service number of an officer (e.g., if the last service number is 57616, new officers will be 57617, 57618, etc.)

### 10. Account Number and RSA PIN Number Changing Workflow

**Officer Submission Phase:**
1. **Officer sends new account number** - The officer sends the new account number to the Accounts Section
2. **Officer sends new RSA PIN** - The officer sends the new RSA PIN number to the Accounts Section (RSA PIN has the prefix PEN)
3. **System receives request** - System receives the change request from the officer

**Verification Phase:**
1. **Account Officer receives** - Account Officer receives the change request
2. **Account Officer verifies** - The Account Officer verifies the new account number and RSA PIN number
3. **Account Officer approves/rejects** - Account Officer approves or rejects the change request
4. **System updates records** - If approved, system updates the officer's account number and RSA PIN records
5. **Officer notified** - Officer receives notification of approval or rejection

### 11. TRADOC Training Results Upload Workflow

1. **Training completion** - Officers complete training after onboarding using their appointment numbers (CDT/RCT prefixes)
2. **TRADOC prepares CSV** - TRADOC prepares training results in CSV format
3. **CSV format requirements** - CSV must contain:
   - Appointment Number (CDT or RCT prefix)
   - Officer Name
   - Training Score/Percentage
   - Training Status (Pass/Fail)
   - **Substantive Rank** (automatically populated from officer record)
4. **TRADOC uploads CSV** - TRADOC uploads the CSV file to the system
5. **System validates** - System validates CSV format and data
6. **System populates rank** - System automatically populates substantive rank from officer record based on appointment number
7. **System sorts by performance** - System automatically sorts officers by performance (highest to lowest) within each rank
8. **System groups by rank** - System groups training results by substantive rank for rank-based service number assignment
9. **System sends to Establishment** - Grouped and sorted list (by rank, then by performance) is sent to Establishment unit for rank-based service number assignment

### 12. ICT Email Management Workflow

1. **Service numbers assigned** - After Establishment assigns service numbers based on training performance
2. **ICT receives notification** - ICT receives notification of new officers with assigned service numbers
3. **ICT creates email addresses** - ICT creates new email addresses on customs.gov.ng domain for each officer
4. **Email format** - Email format follows: [service_number]@customs.gov.ng or similar pattern
5. **ICT deletes personal email** - ICT deletes the former personal email address from the system
6. **System updates officer record** - System updates officer record with new customs.gov.ng email
7. **Officer notified** - Officer is notified of their new email address

### 13. Next of KIN Details Changing Workflow

**Officer Management Phase:**
1. **Officer can add** - The officer can add new Next of KIN with the following details:
   - Name
   - Relationship
   - Phone Number
   - Address
   - Email
2. **Officer can edit** - The officer can edit existing Next of KIN details
3. **Officer can delete** - The officer can delete Next of KIN records
4. **System receives request** - System receives the change request from the officer

**Verification Phase:**
1. **Welfare Officer receives** - Welfare Officer receives the change request
2. **Welfare Officer verifies** - The Welfare Officer verifies the Next of KIN details
3. **Welfare Officer approves/rejects** - Welfare Officer approves or rejects the change request
4. **System updates records** - If approved, system updates the officer's Next of KIN records
5. **Officer notified** - Officer receives notification of approval or rejection

### 12. Investigation Workflow

**Investigation Invitation Phase:**
1. **Investigation Officer searches** - Investigation Officer searches for any officer system-wide by service number, name, or email
2. **Investigation Officer selects officer** - Investigation Officer selects the officer to investigate
3. **Investigation Officer sends invitation** - Investigation Officer sends investigation invitation message to the officer
4. **System sends notifications** - System sends email and in-app notification to the officer about the investigation invitation
5. **Investigation record created** - System creates investigation record with status: INVITED

**Investigation Status Management Phase:**
1. **Investigation Officer reviews case** - Investigation Officer reviews investigation details
2. **Investigation Officer updates status** - Investigation Officer can place officer on one of the following statuses:
   - **Ongoing Investigation** - Officer is actively under investigation (ongoing_investigation = true)
   - **Interdiction** - Officer is interdicted (interdicted = true)
   - **Suspension** - Officer is suspended (suspended = true)
3. **System updates officer record** - System updates officer's investigation status in database
4. **System sends notification** - Officer receives email and in-app notification about status change
5. **System updates promotion eligibility** - System automatically excludes officer from promotion eligibility lists if status is Ongoing Investigation, Interdiction, or Suspension
6. **Accounts receives interdiction list** - If officer is interdicted, they appear on Accounts unit's interdicted officers list

**Investigation Resolution Phase:**
1. **Investigation Officer resolves case** - Investigation Officer can remove investigation status when case is resolved
2. **System updates status** - System updates officer record (ongoing_investigation = false, interdicted = false, suspended = false)
3. **System sends notification** - Officer receives notification about status removal
4. **System updates promotion eligibility** - Officer becomes eligible for promotion again (if other criteria met)
5. **Investigation record closed** - Investigation record is marked as resolved with resolution date

**Investigation History:**
- All investigation records are maintained with complete audit trail
- Investigation history includes: invitation date, status changes, resolution date, investigation officer details
- Investigation records are visible to Investigation Unit and HRD
- Officers can view their own investigation status and history

**Impact on Promotion Eligibility:**
- Officers with Ongoing Investigation, Interdiction, or Suspension status are automatically excluded from Promotion Eligibility Lists
- System checks these statuses when generating eligibility lists
- Status changes automatically trigger re-evaluation of promotion eligibility

**Impact on Accounts:**
- Interdicted officers appear on Accounts unit's interdicted officers list
- Accounts can generate reports of all interdicted officers
- Interdiction status affects payment processing and financial records

### 13. Query Workflow

**Query Issuance Phase:**
1. **Staff Officer searches officer** - Staff Officer searches for an officer within their command
2. **Staff Officer issues query** - Staff Officer issues a query to the officer
3. **Staff Officer provides writeup** - Staff Officer must provide a written reason(s) for querying the officer
4. **System sends notification** - System sends email and in-app notification to the officer about the query
5. **Query status** - Query status is set to PENDING_RESPONSE

**Officer Response Phase:**
1. **Officer receives notification** - Officer receives email and in-app notification about the query
2. **Officer views query** - Officer views the query details and reason(s) provided by Staff Officer
3. **Officer responds** - Officer provides a written response to the query
4. **Officer submits response** - Officer submits response to the query
5. **Query status** - Query status changes to PENDING_REVIEW

**Staff Officer Review Phase:**
1. **Staff Officer receives notification** - Staff Officer receives notification that officer has responded
2. **Staff Officer reviews response** - Staff Officer reviews the officer's response to the query
3. **Staff Officer decision** - Staff Officer makes a decision:
   - **Reject Query** - If satisfied with the officer's response, Staff Officer clicks "Reject Query"
     - Query is closed and does not go into officer's record
     - Query status changes to REJECTED
     - Officer receives notification of query rejection
   - **Accept Query** - If not satisfied with the officer's response, Staff Officer clicks "Accept Query"
     - Query becomes part of officer's disciplinary record
     - Query status changes to ACCEPTED
     - Query is displayed in officer's profile
     - Query is displayed in officer's profile in HRD view
     - Officer receives notification that query has been accepted and added to record

**Record Management:**
1. **Accepted queries stored** - Accepted queries are permanently stored in officer's disciplinary record
2. **Profile display** - Accepted queries are visible in:
   - Officer's personal profile
   - Officer's profile as viewed by HRD
   - Officer's profile as viewed by Area Controller
   - Officer's profile as viewed by DC Admin
3. **Query history** - All queries (pending, accepted, rejected) are maintained in query history
4. **Audit trail** - Complete audit trail maintained with timestamps for:
   - Query issuance date and time
   - Response submission date and time
   - Acceptance/rejection date and time
   - Staff Officer who issued and reviewed the query

**Authority Access:**
1. **HRD Access** - HRD can view all accepted queries system-wide:
   - Dedicated Query Management section in sidebar
   - Filter by command and officer
   - View queries for all officers across all commands
   - Access query details and link to officer profiles
2. **Area Controller Access** - Area Controller can view accepted queries for their command:
   - Dedicated Query Management section in sidebar
   - Filter by officer within their command
   - View queries only for officers in their assigned command
   - Access query details for command officers
3. **DC Admin Access** - DC Admin can view accepted queries for their command:
   - Dedicated Query Management section in sidebar
   - Filter by officer within their command
   - View queries only for officers in their assigned command
   - Access query details for command officers

**Notifications:**
- Email notifications sent at each stage (query issued, response submitted, query accepted/rejected)
- In-app notifications for real-time updates
- Staff Officer receives notifications when officer responds
- Officer receives notifications for query status changes
- **Area Controller and DC Admin** receive email and in-app notifications when queries are accepted in their command
- **HRD** receives in-app notifications only (no email) when queries are accepted anywhere in the system

### 14. NCS Employee App (Social Media Application) Workflow

**Automatic Room Assignment:**
1. **HRD onboards** - Onboarding is done by HRD
2. **On onboarding** - On onboarding: Officer added to command chat room
3. **Command chat rooms** - The application will have a command chat room that only selects officers of a command
4. **On posting** - On posting: Officer moved from old to new command chat
5. **Automatic transfer** - When an officer is posted out, he goes to his new command chat room and leaves the previous one
6. **Management groups created** - Management groups created for AC and above
7. **Staff Officer can add** - Staff Officer can manually add officers to groups

**Management Groups:**
- Management Groups are automatically created in a command from an Assistant Comptroller and above
- Staff Officer can add any officer to unit (As some units heads are below Chief Superintendent and also Legal department that are non officer can be added but they must be onboarded by HRD)

**Features Available:**
- Command-specific discussions
- Real-time messaging
- File and document sharing
- Broadcast messages from HRD
- Profile picture management

### 15. Admin Role Assignment Workflow

**HRD Assignment Phase:**
1. **HRD accesses Role Assignments Management** - HRD navigates to Role Assignments Management section
2. **HRD selects Assign Role to Officer** - HRD selects "Assign Role to Officer" option
3. **HRD selects officer** - HRD searches and selects the officer to be assigned Admin role
4. **HRD selects command** - HRD selects the specific command for which the Admin role will be assigned
5. **HRD assigns Admin role** - HRD assigns the Admin role to the selected officer for the selected command
6. **System updates records** - System updates officer's role assignment and links Admin role to the command
7. **Officer notified** - Officer receives notification of Admin role assignment

**Admin Role Activation:**
1. **Admin accesses system** - Assigned Admin logs into the system
2. **Admin views command scope** - Admin can only see and manage role assignments for their assigned command
3. **Admin performs role assignments** - Admin can now assign roles within their command:
   - Role Assignments Staff Officer
   - Role Assignments Area Comptroller or Unit Head
   - Role Assignments DC Admin
4. **System validates scope** - System ensures Admin can only assign roles within their assigned command
5. **Audit trail maintained** - All role assignments by Admin are logged with command scope

**Note:** Admin role assignments are command-specific. An Admin assigned to a command can only manage role assignments within that command's scope.

---

## Application Flows Based on Role Interactions

### FLOW 1: EMOLUMENT PROCESS

**Timeline Creation Phase:**
1. HRD creates emolument timeline with start and end dates
2. System activates timeline and notifies all officers
3. Cron job monitors for automatic extensions if configured

**Officer Submission Phase:**
1. Officers log in during active timeline
2. Officers view previous year's data for reference
3. Officers update editable fields (Banking, PFA, Next of Kin)
4. Officers submit emolument (Status: RAISED)

**Assessment Phase:**
1. Assessor receives notification of raised emoluments
2. Assessor reviews only subordinate officers' submissions
3. Assessor verifies all information accuracy
4. Assessor approves assessment (Status: ASSESSED)

**Validation Phase:**
1. Validator/Area Controller receives assessed emoluments
2. Validator performs final review and compliance check
3. Validator approves for payment (Status: VALIDATED)

**Payment Phase:**
1. Accounts generates validated officers report
2. Accounts extracts payment information
3. Accounts processes payments (Status: PROCESSED)

---

### FLOW 2: LEAVE APPLICATION PROCESS

**Application Phase:**
1. Officer checks leave eligibility in system
2. Officer selects from 28 leave types
3. Officer submits application with dates and reason
4. System calculates days and validates eligibility

**Processing Phase:**
1. Staff Officer receives leave application
2. Staff Officer reviews supporting documents
3. Staff Officer minutes to DC Admin
4. Application status changes to MINUTED

**Approval Phase:**
1. DC Admin reviews minuted application
2. DC Admin approves or rejects with reason
3. If approved, Area Controller's name appears on document
4. Status changes to APPROVED

**Distribution Phase:**
1. Staff Officer prints approved leave document
2. Staff Officer distributes to officer
3. System sends automatic notification to officer
4. System monitors leave (alerts 72 hours before end)

---

### FLOW 3: PASS APPLICATION PROCESS

**Special Rules:**
- Maximum 5 days only
- Annual leave must be exhausted
- Maximum 2 passes per year

**Process Flow:**
1. Officer applies for pass (system validates eligibility)
2. Staff Officer processes and minutes to DC Admin
3. DC Admin approves (ensures 5-day limit)
4. Staff Officer prints and distributes
5. System monitors pass duration
6. Automatic alert sent on expiry date

---

### FLOW 4: OFFICER ONBOARDING PROCESS

**For New Recruits:**
1. Establishment creates recruit with rank and GL level
2. System automatically assigns appointment number with prefix (CDT for ASC II GL 08+, DSC / RCT for IC GL 07-, AIC)
3. System generates onboarding link
4. Email sent to new officer (personal email)
5. Officer receives email and clicks link
6. Officer fills onboarding form and uploads credentials
7. Officer accepts disclaimer about false information
8. Officers undergo training using appointment numbers (CDT/RCT prefixes)
9. TRADOC uploads training results (CSV format with substantive rank, sorted by performance)
10. System groups training results by rank
11. Establishment receives grouped list (by rank, sorted by performance within each rank)
12. System assigns service numbers grouped by rank (each rank maintains its own sequence, performance-based within rank)
13. ICT creates new email addresses on customs.gov.ng
14. ICT deletes former personal email addresses
15. System creates account and assigns to command chat

**For Existing Officers:**
1. HRD initiates onboarding
2. HRD enters officer email
3. System sends unique onboarding link

**Officer Completion:**
1. Officer receives email and clicks link
2. Officer completes multi-step form:
   - Step 1: Personal Information
   - Step 2: Employment Details  
   - Step 3: Banking and Pension
   - Step 4: Next of Kin and Documents
3. Officer accepts disclaimer about false information
4. System creates account and assigns to command chat

---

### FLOW 5: MANNING LEVEL REQUEST PROCESS

**Request Creation:**
1. Staff Officer analyzes command needs
2. Staff Officer creates request specifying:
   - Ranks needed and quantities
   - Sex requirements (if any)
   - Qualification requirements (optional)
3. Staff Officer submits to Area Controller

**Approval Process:**
1. Area Controller reviews justification
2. Area Controller approves and forwards to HRD

**Matching Process:**
1. HRD receives approved request
2. System searches for matching officers
3. System filters by rank, sex, qualification, status
4. HRD selects from matched candidates
5. HRD generates movement orders

**Implementation:**
1. Officers receive posting notifications
2. New command Staff Officer sees officer on dashboard
3. Staff Officer documents arrival
4. System updates nominal rolls and chat rooms

---

### FLOW 6: PROMOTION ELIGIBILITY PROCESS

**Setup Phase:**
1. HRD sets years required in each rank
2. System stores promotion criteria

**Generation Phase:**
1. System automatically checks eligibility
2. System filters out:
   - Officers not meeting time in rank
   - Interdicted officers
   - Suspended officers
   - Officers under investigation
   - Deceased officers
3. System generates eligibility list

**Board Review:**
1. Board receives eligibility list
2. Board conducts promotion exercise
3. Board makes promotion decisions
4. Board updates ranks in system

**Implementation:**
1. System updates officer records
2. New rank and date recorded
3. Officers receive promotion notifications

---

### FLOW 7: RETIREMENT PROCESS

**Automatic Detection:**
1. System daily checks for:
   - Officers reaching age 60
   - Officers completing 35 years service
2. System generates retirement list

**Retirement Date Calculation:**
1. System calculates for each officer:
   - Age-based retirement: Date of Birth + 60 years
   - Service-based retirement: Date of First Appointment + 35 years
   - Actual retirement date: Whichever comes earlier (AGE or SVC)

**3-Month Alert System:**
1. System checks daily for officers approaching retirement (3 months before)
2. System sends alert notification to officer
3. Officer dashboard displays retirement information and countdown
4. System tracks alert status to prevent duplicate notifications

**Automatic Preretirement Leave Placement:**
1. System detects officers reaching 3 months before retirement date
2. System automatically places officer on preretirement leave
3. System sends notification to officer about preretirement leave placement
4. Officer status updated to "ON_PRERETIREMENT_LEAVE"
5. System records automatic placement date

**CGC Preretirement Leave Management:**
1. CGC searches for officers approaching preretirement leave
2. CGC views list of officers on preretirement leave
3. CGC can approve officers to work during preretirement period:
   - CGC selects officer and approves "preretirement leave in office"
   - Officer status changes to "PRERETIREMENT_LEAVE_IN_OFFICE"
   - Officer can continue working for last 3 months before retirement
   - CGC approval recorded with timestamp
4. CGC can view comprehensive preretirement leave reports

**Processing:**
1. HRD reviews retirement list
2. System calculates pre-retirement leave (3 months prior)
3. System automatically places officers on preretirement leave
4. System activates pre-retirement status
5. Notifications sent to:
   - Retiring officer (automatic preretirement leave placement notification)
   - CGC (notification of officers placed on preretirement leave)
   - Accounts (for benefits processing)
   - Welfare (for transition support)

---

### FLOW 8: DECEASED OFFICER PROCESS

**Reporting:**
1. Area Controller or Staff Officer reports death
2. System flags as potentially deceased
3. Welfare receives notification

**Validation:**
1. Welfare validates death certificate
2. Welfare confirms deceased status
3. System updates all relevant records

**Benefits Processing:**
1. Welfare generates comprehensive deceased data
2. Accounts calculates entitlements
3. Accounts processes payments to next of kin
4. Welfare provides support to beneficiaries

---

### FLOW 9: STAFF/MOVEMENT ORDER PROCESS

**Staff Order (Individual Posting):**
1. HRD searches for specific officer
2. HRD changes posting location
3. Officer receives movement notification
4. Old command removes from roll
5. New command adds to roll

**Movement Order (Bulk Posting):**
1. HRD sets criteria (time at station in months)
2. System identifies eligible officers
3. HRD reviews and confirms postings
4. Multiple officers receive notifications
5. Automatic nominal roll updates

---

### FLOW 10: NCS EMPLOYEE APP CHAT MANAGEMENT

**Automatic Room Assignment:**
1. On onboarding: Officer added to command chat room
2. On posting: Officer moved from old to new command chat
3. Management groups created for AC and above
4. Staff Officer can manually add officers to groups

**Features Available:**
- Command-specific discussions
- Real-time messaging
- File and document sharing
- Broadcast messages from HRD
- Profile picture management

---

### FLOW 11: ACCOUNT NUMBER AND RSA PIN CHANGE PROCESS

**Officer Submission Phase:**
1. Officer submits new account number to Accounts Section
2. Officer submits new RSA PIN number to Accounts Section (RSA PIN has the prefix PEN)
3. System receives change request

**Verification Phase:**
1. Account Officer receives change request
2. Account Officer verifies the new account number and RSA PIN number
3. Account Officer approves or rejects the change request
4. If approved, system updates officer's records
5. Officer receives notification of approval or rejection

---

### FLOW 12: NEXT OF KIN DETAILS CHANGE PROCESS

**Officer Management Phase:**
1. Officer adds, edits, or deletes Next of KIN details (Name, Relationship, Phone Number, Address, Email)
2. System receives change request

**Verification Phase:**
1. Welfare Officer receives change request
2. Welfare Officer verifies the Next of KIN details
3. Welfare Officer approves or rejects the change request
4. If approved, system updates officer's Next of KIN records
5. Officer receives notification of approval or rejection

---

### FLOW 13: INVESTIGATION PROCESS

**Investigation Invitation Phase:**
1. Investigation Officer searches for officer system-wide
2. Investigation Officer selects officer and sends investigation invitation message
3. System sends email and in-app notification to officer
4. Investigation record created with INVITED status

**Investigation Status Management Phase:**
1. Investigation Officer reviews case and updates status:
   - Ongoing Investigation (ongoing_investigation = true)
   - Interdiction (interdicted = true)
   - Suspension (suspended = true)
2. System updates officer record and sends notification
3. System automatically excludes officer from promotion eligibility lists
4. If interdicted, officer appears on Accounts interdicted officers list

**Investigation Resolution Phase:**
1. Investigation Officer resolves case and removes status
2. System updates officer record and sends notification
3. Officer becomes eligible for promotion again (if other criteria met)
4. Investigation record marked as resolved

**Impact:**
- Officers with investigation statuses cannot appear on Promotion Eligibility Lists
- Interdicted officers appear on Accounts unit's interdicted officers list
- Status changes trigger automatic re-evaluation of promotion eligibility

### FLOW 14: QUERY PROCESS

**Query Issuance Phase:**
1. Staff Officer searches for officer within command
2. Staff Officer issues query with written reason(s)
3. System sends email and in-app notification to officer
4. Query status: PENDING_RESPONSE

**Officer Response Phase:**
1. Officer receives notification (email and in-app)
2. Officer views query details and reason(s)
3. Officer provides written response
4. Officer submits response
5. Query status: PENDING_REVIEW

**Staff Officer Review Phase:**
1. Staff Officer receives notification of response
2. Staff Officer reviews officer's response
3. Staff Officer makes decision:
   - **Reject Query** (if satisfied):
     - Query closed, not added to record
     - Status: REJECTED
     - Officer notified
   - **Accept Query** (if not satisfied):
     - Query added to officer's disciplinary record
     - Status: ACCEPTED
     - Visible in officer's profile and HRD view
     - Officer notified

**Record Management:**
1. Accepted queries permanently stored in disciplinary record
2. Visible in officer profile and HRD profile view
3. Complete audit trail with timestamps
4. Query history maintained for all statuses

### FLOW 15: DUTY ROSTER WORKFLOW

**Roster Creation Phase:**
1. **Staff Officer creates roster** - Staff Officer creates a new duty roster for their command
2. **Set period** - Staff Officer sets roster period (start and end dates)
3. **Initial status** - Roster is created with status: DRAFT
4. **No assignments yet** - Roster can be created without assignments initially

**Assignment Phase:**
1. **Staff Officer edits roster** - Staff Officer edits the DRAFT roster to add assignments
2. **Select OIC** - Staff Officer selects Officer in Charge (OIC) - **Required**
3. **Select 2IC** - Staff Officer selects Second In Command (2IC) - **Optional**
4. **Add assignments** - Staff Officer adds officer assignments with:
   - Officer selection
   - Duty date (must be within roster period)
   - Shift (optional: Morning, Evening, Night, etc.)
   - Notes (optional)
5. **Save assignments** - Staff Officer saves the roster with assignments
6. **System notifies officers** - **All assigned officers receive email and in-app notifications**:
   - OIC receives notification indicating they are Officer in Charge
   - 2IC receives notification indicating they are Second In Command
   - Other officers receive notification with OIC and 2IC information
   - Notifications include roster period, command, and role assignment

**Submission Phase:**
1. **Staff Officer submits** - Staff Officer submits roster for approval (requires at least 1 assignment)
2. **Status changes** - Roster status changes to SUBMITTED
3. **System notifies approvers** - **DC Admin and Area Controller receive email and in-app notifications**:
   - Notification includes command name, period, prepared by, and assignment count
   - Both DC Admin and Area Controller can approve/reject independently
4. **Roster becomes read-only** - Staff Officer can view but cannot edit submitted roster

**Approval Phase (Dual Approval):**
1. **DC Admin reviews** - DC Admin views submitted rosters on dashboard (filtered by command if assigned)
2. **DC Admin sees details** - DC Admin views:
   - Command name and period
   - OIC and 2IC assignments
   - All officer assignments with dates, shifts, and notes
   - Prepared by information
3. **DC Admin approves/rejects** - DC Admin can:
   - **Approve**: Changes status to APPROVED, sets approved_at timestamp
   - **Reject**: Requires rejection reason, changes status to REJECTED
4. **Area Controller reviews** - Area Controller views submitted rosters (all commands)
5. **Area Controller approves/rejects** - Area Controller can:
   - **Approve**: Changes status to APPROVED, sets approved_at timestamp
   - **Reject**: Requires rejection reason, changes status to REJECTED
6. **Independent approval** - Both DC Admin and Area Controller can approve independently
   - Either approver can approve the roster
   - Either approver can reject with reason

**Notification Details:**
- **When assignments are added/updated**: All assigned officers notified (email + in-app)
- **When roster is submitted**: DC Admin and Area Controller notified (email + in-app)
- **Notification content includes**:
  - Roster period (start - end dates)
  - Command name
  - Officer's role (OIC, 2IC, or Regular Officer)
  - OIC and 2IC names (for regular officers)
  - Link to view roster details

**Roster Leadership Structure:**
- **Officer in Charge (OIC)**: Required, primary leadership role
- **Second In Command (2IC)**: Optional, secondary leadership role
- **Regular Officers**: All other assigned officers
- OIC and 2IC are displayed prominently in roster views
- OIC and 2IC badges shown in assignment tables

**Status Flow:**
```
DRAFT → SUBMITTED → APPROVED
         ↓
      REJECTED
```

**Dashboard Integration:**
- **DC Admin Dashboard**: Shows pending rosters count and recent rosters widget
- **Area Controller Dashboard**: Shows pending rosters count and recent rosters widget
- **Quick Actions**: Direct links to review and approve rosters
- **Real-time Updates**: Dashboard widgets update when new rosters are submitted

---

## Critical Interaction Points

### Emolument Chain of Command:
Officer (Raises) → Assessor (Reviews) → Validator (Approves) → Accounts (Pays)

### Leave Approval Chain:
Officer (Applies) → Staff Officer (Minutes) → DC Admin (Approves) → Staff Officer (Distributes)

### Manning Request Chain:
Staff Officer (Requests) → Area Controller (Approves) → HRD (Processes) → Officers (Posted)

### Posting Implementation Chain:
HRD (Initiates) → Officers (Notified) → Staff Officer (Documents) → System (Updates Rolls)

### Deceased Benefits Chain:
Reporter (Indicates) → Welfare (Validates) → Accounts (Calculates) → Next of Kin (Receives)

### Account Number and RSA PIN Change Chain:
Officer (Submits) → Accounts (Verifies) → System (Updates) → Officer (Notified)

### Next of KIN Change Chain:
Officer (Manages) → Welfare (Verifies) → System (Updates) → Officer (Notified)

### Query Chain:
Staff Officer (Issues Query) → Officer (Responds) → Staff Officer (Reviews) → System (Updates Record if Accepted) → Officer & HRD (View in Profile)

### Investigation Chain:
Investigation Unit (Sends Invitation) → Officer (Receives Notification) → Investigation Unit (Places on Status) → System (Updates Officer Record) → System (Excludes from Promotion Eligibility) → Accounts (Receives Interdicted List if Interdicted)

### Duty Roster Chain:
Staff Officer (Creates & Assigns) → System (Notifies All Officers) → Staff Officer (Submits) → System (Notifies DC Admin & Area Controller) → DC Admin/Area Controller (Approve/Reject) → System (Updates Status)

---

## Leave Types

### Standard Leave Types

1. **Annual Leave**
   - Can be applied in parts but maximum of 2 times in a year within the stipulated calendar days for officers
   - Duration: 28 Days for GL 07 and Below, 30 days for Level 08 and above

2. **Leave on Permanent invalidation**
   - 2 months to be recommended by a Medical Officer

3. **Deferred Leave**
   - (No specific duration mentioned)

4. **Casual Leave**
   - 7 working days
   - Must be when you've exhausted your Annual leave

5. **Sick Leave**
   - Must be recommended by a medical officer
   - No duration specified

6. **Maternity Leave**
   - 112 working days a year
   - There should be a space where she can supply her Expected Date of Delivery (EDD)

7. **Maternity Leave on adoption of Child Under 4 months**
   - 84 working days a year

8. **Paternity Leave**
   - 14 working days a year

9. **Paternity Leave on adoption of Child Under 4 months**
   - 14 working days a year

10. **Examination Leave**
    - (No specific duration mentioned)

11. **Leave for Compulsory Examination**
    - (No specific duration mentioned)

12. **Leave for non compulsory examination**
    - (No specific duration mentioned)

13. **Sabbatical Leave**
    - 12 calendar months once every 5 years

14. **Study Leave without pay**
    - 4 years in the first instance but you can apply for an extension

15. **Study Leave with pay**
    - 3 years with an extension of 1 year

16. **Leave on compassionate grounds**
    - (No specific duration mentioned)

17. **Pre-retirement leave**
    - Duration: 3 months before retirement date (automatic)
    - **IMPORTANT:** Officers cannot apply for preretirement leave
    - Preretirement leave is automatically activated 3 months before retirement date
    - Only CGC can approve officers to continue working during preretirement period (preretirement leave "in office")
    - Officers are automatically notified and placed on preretirement leave
    - CGC has exclusive authority to manage preretirement leave approvals

18. **Leave of absence**
    - (No specific duration mentioned)

19. **Leave on grounds on urgent private affairs**
    - (No specific duration mentioned)

20. **Leave for cultural and sporting activities**
    - (No specific duration mentioned)

21. **Leave to take part in trade Union activities**
    - (No specific duration mentioned)

22. **Leave for in-service training**
    - 4 years maximum

23. **Leave of absence to Join Spouse on course of instruction abroad**
    - 9 months

24. **Special leave to join spouse on ground of public policy**
    - (No specific duration mentioned)

25. **Leave of absence on grounds of public policy for technical aid program**
    - (No specific duration mentioned)

26. **Leave of absence for Special or Personal assistants on ground of public policy**
    - (No specific duration mentioned)

27. **Leave of absence for Spouse of President, Vice President, Governor and Deputy Governor**
    - (No specific duration mentioned)

**Note:** HRD can create a type of leave too and assign the duration.

---

## Business Rules & Constraints

### General Rules

1. **Document Upload Format**
   - When officers are onboarding or new recruits, they must upload documents preferably in JPEG to save space

2. **Onboarding Caveat**
   - Any false information provided by an officer can lead to Dismissal for Forgery under the PSR Rules

3. **Pass Rules**
   - Maximum number of days given to an officer for pass should be 5 days
   - Pass can only be applied if and only if the officer has exhausted the Annual leave

4. **Annual Leave Rules**
   - Can be applied for a maximum of 2 times in a year
   - Duration: 28 Days for GL 07 and Below, 30 days for Level 08 and above

5. **Emolument Rules**
   - Emolument is done once a year
   - HRD creates timeline with Start and End date (can be extended by HRD, Cron Job)
   - Officers can only login during the timeline period to raise emolument

6. **RSA PIN Format**
   - Retirement Savings Account (RSA) PIN usually has a prefix PEN
   - RSA PIN is usually 12 digits

### Retirement Rules

1. **Retirement Criteria**
   - An officer is due for retirement when he reaches the age of 60 years or 35 years in service, whichever comes first
   - Retirement date is calculated as: min(Date of Birth + 60 years, Date of First Appointment + 35 years)

2. **3-Month Alert System**
   - System automatically alerts officers 3 months before their retirement date
   - Alert is sent once when officer reaches 3 months before retirement
   - Officer dashboard displays retirement countdown and information
   - Officers can view their calculated retirement date and retirement type (AGE or SVC)

3. **Pre-Retirement Leave**
   - Pre-Retirement Leave is 3 months before retirement date
   - Date of Pre Retirement Leave (DOPR) = Retirement Date - 3 months
   - **Automatic Placement:** Officers are automatically placed on preretirement leave 3 months before retirement
   - **Automatic Notification:** Officers are automatically notified when preretirement leave begins
   - **CGC Approval Exception:** Only CGC can approve officers to continue working during preretirement period (preretirement leave "in office")
   - **Officer Restrictions:** Officers cannot apply for preretirement leave - it is automatic and CGC-managed only
   - **Default Status:** All officers are automatically placed on preretirement leave unless CGC approves them to work
   - **CGC Authority:** CGC has exclusive authority to search officers, review preretirement status, and approve exceptions

### Investigation Rules

1. **Investigation Status Management**
   - Investigation Unit can search any officer system-wide
   - Investigation Unit can send investigation invitation messages to officers
   - Investigation Unit can place officers on the following statuses:
     - **Ongoing Investigation** (ongoing_investigation = true)
     - **Interdiction** (interdicted = true)
     - **Suspension** (suspended = true)
   - Officers receive email and in-app notifications when investigation status changes
   - Investigation records maintain complete audit trail with timestamps

2. **Investigation Status Impact**
   - Officers with Ongoing Investigation, Interdiction, or Suspension cannot appear on Promotion Eligibility Lists
   - Interdicted officers appear on Accounts unit's interdicted officers list
   - Investigation status changes automatically trigger re-evaluation of promotion eligibility
   - Investigation status affects officer's career progression activities

3. **Investigation Resolution**
   - Investigation Unit can remove investigation status when cases are resolved
   - Officers become eligible for promotion again after status removal (if other criteria met)
   - Investigation records are maintained permanently for audit purposes

### Promotion Rules

1. **Eligibility Exclusions**
   - Officers with the following statuses will NOT feature on the Eligibility List even if they are due for promotion:
     - Interdicted (set by Investigation Unit)
     - Suspended (set by Investigation Unit)
     - Dismissed (set by Discipline seat)
     - Has ongoing investigation (ongoing_investigation set by Investigation Unit)
     - Deceased (set by Welfare seat)

2. **Rank Changes**
   - Rank changes are done by Board
   - Officers cannot edit their own rank

### Validation Rules

1. **Emolument Validation Flow**
   - Assessor accesses officers under his command who have raised Emolument
   - Validator validates officers that the Assessor has assessed
   - Area Controller validates officers (required before Accounts can generate lists)

2. **Deceased Officer Validation**
   - Area Controller or Staff Role can indicate that an officer is deceased
   - Welfare validates when an officer is deceased
   - Welfare generates data on deceased officers

### Service Number Rules

1. **Appointment Number Assignment (Training Phase)**
   - Appointment numbers are assigned by Establishment unit with automatic prefix determination
   - **CDT prefix** assigned for:
     - ASC II GL 08 and above
     - DSC ranks
   - **RCT prefix** assigned for:
     - IC GL 07 and below
     - AIC ranks
   - Sequential numbering per prefix (e.g., CDT00001, CDT00002, RCT00001, RCT00002)
   - Used throughout training period

2. **Service Number Allocation (Post-Training)**
   - Service Number allocation is done by Establishment unit
   - **Rank-based assignment**: Service numbers are assigned grouped by rank
   - Each rank maintains its own independent service number sequence
   - System automatically tracks last service number per rank
   - Within each rank, service numbers assigned based on training performance (highest to lowest)
   - Example: If IC rank last service number is NCS65000, next IC officer gets NCS65001
   - Example: If ASC II rank last service number is NCS65200, next ASC II officer gets NCS65201
   - Different ranks have completely independent sequences

3. **For Existing Officers (Sequential)**
   - Any new officer documented will start from the last service number + 1 (sequential numbering)
   - Example: If last service number is 57616, new officers will be 57617, 57618, etc.

### Posting and Transfer Rules

1. **Automatic Updates**
   - When an officer is posted, their name automatically goes off the previous command's nominal roll
   - Officer appears on the dashboard of the Staff Officer of the new posting
   - Officer automatically transfers to new command chat room and leaves previous one

2. **Staff Orders**
   - HRD can search for officer and change posting at any time
   - HRD can alter Staff Orders and Movement Orders

3. **Movement Orders**
   - HRD enters criteria based on time spent (in Months)
   - HRD can use Manning Level for postings

### Notification Rules

1. **Pass Notifications**
   - System alerts the officer when the pass ends

2. **Leave Notifications**
   - System alerts the officer when the Leave is 72 hours to the end

3. **Approval Notifications**
   - Officer receives automatic message to go for pass or leave after approval

### NCS Employee App Rules

1. **Chat Rooms**
   - Command chat rooms only include officers of that command
   - Automatic transfer when officer is posted

2. **Management Groups**
   - Automatically created in a command from Assistant Comptroller and above
   - Staff Officer can add officers to unit (for unit heads below Chief Superintendent and Legal department non-officers)
   - Non-officers must be onboarded by HRD

### Leave Type Management

1. **HRD Authority**
   - HRD can create new types of leave and assign the duration

### Query Management Rules

1. **Query Issuance**
   - Staff Officer can only issue queries to officers within their assigned command
   - Staff Officer must provide written reason(s) for issuing a query
   - Query reason(s) are mandatory and cannot be empty

2. **Query Response**
   - Officers must respond to queries issued to them
   - Response is mandatory before Staff Officer can review
   - Officers can view query history and status in their profile

3. **Query Acceptance/Rejection**
   - Only Staff Officer who issued the query can accept or reject the response
   - Rejected queries do not become part of officer's disciplinary record
   - Accepted queries are permanently added to officer's disciplinary record
   - Accepted queries are visible in officer's profile and HRD profile view

4. **Query Record Management**
   - Accepted queries form part of officer's permanent disciplinary record
   - Query history includes all queries (pending, accepted, rejected)
   - Complete audit trail maintained with timestamps for all query actions
   - Queries cannot be deleted once accepted

5. **Notifications**
   - Email and in-app notifications sent at each stage:
     - When query is issued to officer
     - When officer submits response
     - When Staff Officer accepts or rejects query
   - Real-time notifications ensure timely communication
   - **Area Controller and DC Admin** receive email and in-app notifications when queries are accepted in their command
   - **HRD** receives in-app notifications only (no email) when queries are accepted anywhere in the system

6. **Authority Access to Disciplinary Records**
   - HRD can view all accepted queries system-wide through dedicated Query Management section
   - Area Controller can view accepted queries for officers in their assigned command only
   - DC Admin can view accepted queries for officers in their assigned command only
   - All accepted queries are displayed in officer profile pages when viewed by HRD, Area Controller, or DC Admin
   - Command-based roles (Area Controller, DC Admin) are restricted to their command's queries only
   - HRD has system-wide access to all queries

---

## Key Optimizations Implemented

### 1. Streamlined Approval Processes
- Reduced bottlenecks by clear role separation
- Automated status tracking at each stage
- Real-time notifications to next approver

### 2. Automated Workflows
- Cron jobs for timeline management
- Automatic eligibility calculations
- System-generated notifications
- Auto-population of previous data

### 3. Command-Based Boundaries
- Officers can only see their command data
- Reduces data overload
- Improves system performance
- Maintains security

### 4. Status-Driven Processing
- Clear status indicators at each stage
- Prevents duplicate processing
- Easy tracking and reporting
- Audit trail maintenance

### 5. Integrated Communication
- Automatic chat room management
- Role-based group creation
- Seamless posting transitions
- Real-time updates

### 6. Document Management
- Standardized JPEG format
- Compressed storage
- Easy retrieval
- Linked to officer profiles

### 7. Notification System
- Email for critical actions
- In-app for routine updates
- SMS for time-sensitive alerts
- Automated reminders

### 8. Quality Control Checkpoints
- Assessment before validation
- Validation before payment
- Approval before distribution
- Verification at each stage

---

## System Safeguards

### Role Separation:
- No user can approve their own requests
- Assessors cannot validate
- Staff Officers cannot approve
- Clear hierarchical structure

### Data Integrity:
- Automatic backups every 6 hours
- Transaction logs for all changes
- Rollback capabilities
- Audit trail for 7 years

### Access Control:
- Role-based permissions
- Command-level restrictions
- Personal data protection
- Secure authentication

### Process Validation:
- Eligibility checks before applications
- Status verification before transitions
- Duplicate prevention mechanisms
- Error handling at each stage

---

## Performance Indicators

### System Efficiency Metrics:
- **Emolument processing time:** Target 5 days from raise to validation
- **Leave approval cycle:** Target 48 hours
- **Manning request fulfillment:** Target 7 days
- **Movement order implementation:** Target 72 hours
- **Retirement processing:** 30 days advance notification

### User Satisfaction Indicators:
- Self-service capabilities for officers
- Real-time status tracking
- Automated notifications
- Mobile app accessibility
- Document upload simplicity

### Administrative Efficiency:
- Reduced manual data entry
- Automated report generation
- Bulk processing capabilities
- Integration between modules
- Centralized dashboard for each role

---

## Appendix

### Field Definitions

**PFA:** Pension Fund Administrator

**RSA:** Retirement Savings Account

**RSA PIN:** Retirement Savings Account PIN (usually with prefix PEN, 12 digits)

**DOB:** Date of Birth

**DOFA:** Date of First Appointment

**DOPR:** Date of Pre Retirement Leave

**EDD:** Expected Date of Delivery (for Maternity Leave)

**GL:** Grade Level

**LGA:** Local Government Area

**PE Form:** Personal Emolument Form

**PSR Rules:** Public Service Rules

**SVC:** Service (years in service)

**DC:** Deputy Comptroller

**AC:** Assistant Comptroller

