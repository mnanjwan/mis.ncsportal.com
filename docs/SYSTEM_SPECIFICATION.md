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

### Building Unit - Accommodation Manager
**Primary Role:** Officer quarters allocation and management

**Access Level:** Quarters and accommodation records

**Core Functions:**
1. Quarters the Officers (But the status on the PE form will indicate whether the Officers is quartered or not)
2. At the Command level, enters the Quartered status Yes or No option on the drop down
3. Allocate quarters to eligible officers
4. Update quartered status (Yes/No) in the system
5. Maintain quarters occupancy database
6. Process quarter allocation requests
7. Deallocate quarters when officers are posted
8. Track quarters maintenance requirements
9. Generate accommodation reports
10. Manage waiting lists for quarters

### Establishment - Service Number Administrator
**Primary Role:** New officer registration and service number management

**Access Level:** New recruitment and service numbers

**Core Functions:**
1. Onboard new officers
2. Allocate Service Number to new officers (starts from the last service number of an officer, e.g., if the last service number is 57616, new officers will be 57617, 57618, etc.)
3. Maintain service number registry
4. Process new recruit documentation
5. Create initial officer records
6. Coordinate recruitment exercises
7. Process appointment letters
8. Assign initial ranks to new officers
9. Ensure unique identification for all officers

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
3. Extract payment data: Bank Name, Account Number, PFA, RSA
4. Process salary payments and remittances
5. Calculate and process death benefits
6. Process pension remittances
7. Generate payment reports and reconciliations
8. Maintain financial audit trails
9. Handle payment exceptions and corrections
10. Generate payroll and financial statistics

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
1. **Establishment allocates** - Establishment allocates service number
2. **Establishment creates record** - Establishment creates initial record
3. **System generates link** - System generates onboarding link
4. **Email sent** - Email sent to new officer

**For Existing Officers:**
1. **HRD initiates** - HRD initiates onboarding
2. **HRD enters email** - HRD enters officer email
3. **System sends link** - System sends unique onboarding link

**Officer Completion:**
1. **Officer receives email** - Officer receives email and clicks link
2. **Officer fills information** - Officers will fill the following information in multi-step form:
   - Step 1: Personal Information
   - Step 2: Employment Details
   - Step 3: Banking and Pension
   - Step 4: Next of Kin and Documents
   
   **Complete Field List:**
   - Service Number
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
3. **Officer accepts disclaimer** - Officer accepts disclaimer about false information
4. **Upload documents** - Officers must upload documents preferably in JPEG to save space
5. **Caveat displayed** - System displays caveat: "Any false information provided by you can lead to Dismissal for Forgery under the PSR Rules"
6. **System creates account** - System creates account and assigns to command chat
7. **Establishment processes** - Establishment unit processes the onboarding and allocates Service Number

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

1. **Command level entry** - At the Command level, Building Unit will enter the Quartered status Yes or No option on the drop down
2. **Status on PE form** - The status on the PE form will indicate whether the Officers is quartered or not

### 7. Eligibility List for Promotion Workflow

**Setup Phase:**
1. **HRD sets criteria** - The HRD will set the number of years that an officer will stay on the rank to be eligible for promotion
2. **System stores criteria** - System stores promotion criteria

**Generation Phase:**
1. **System checks eligibility** - System automatically checks eligibility
2. **System checks exclusions** - The system will check if the officer has been:
   - Officers not meeting time in rank
   - Interdicted (set by Discipline seat)
   - Suspended (set by Discipline seat)
   - Dismissed (set by Discipline seat)
   - Officers under investigation
   - Has a pending investigation issue (set by the investigation seat)
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
4. **System activates status** - System activates pre-retirement status
5. **Notifications sent** - Notifications sent to:
   - Retiring officer
   - Accounts (for benefits processing)
   - Welfare (for transition support)
6. **Officer informed** - The officer will be informed

### 9. Service Number Allocation Workflow

1. **Establishment allocates** - This is done by establishment unit
2. **Sequential numbering** - Any new officer documented will start from the last service number of an officer (e.g., if the last service number is 57616, new officers will be 57617, 57618, etc.)
3. **Pattern precedence** - This is the flow that will take precedence on any new recruitment, so officers newly recruited will be built in this pattern

### 10. NCS Employee App (Social Media Application) Workflow

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
1. Establishment allocates service number
2. Establishment creates initial record
3. System generates onboarding link
4. Email sent to new officer

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

**Processing:**
1. HRD reviews retirement list
2. System calculates pre-retirement leave (3 months prior)
3. System activates pre-retirement status
4. Notifications sent to:
   - Retiring officer
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
    - (No specific duration mentioned)

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

2. **Pre-Retirement Leave**
   - Pre-Retirement Leave is 3 months before retirement date

### Promotion Rules

1. **Eligibility Exclusions**
   - Officers with the following statuses will NOT feature on the Eligibility List even if they are due for promotion:
     - Interdicted (set by Discipline seat)
     - Suspended (set by Discipline seat)
     - Dismissed (set by Discipline seat)
     - Has a pending investigation issue (set by the investigation seat)
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

1. **Allocation**
   - Service Number allocation is done by Establishment unit
   - New officers start from the last service number + 1 (sequential numbering)
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

