# NCS Employee Portal - System Testing Checklist

## Testing Context
**Role Being Tested:** Assessor (Emolument Reviewer)  
**Date:** Current Testing Session  
**Purpose:** Manual feature testing for all roles

---

## ASSESSOR ROLE - SPECIFIC TESTING CHECKLIST

### 1. Access Control & Permissions
- [ ] **Access Level Verification**
  - [ ] Can only see officers under their command
  - [ ] Cannot see officers from other commands
  - [ ] Can only see officers who have raised emoluments
  - [ ] Cannot see officers who haven't raised emoluments

### 2. Emolument List View
- [ ] **List Display**
  - [ ] Officers appear in a list format
  - [ ] List shows only subordinate officers who have raised emoluments
  - [ ] Each officer entry has a "View Details" button
  - [ ] List is properly filtered by command
  - [ ] List shows relevant officer information (name, service number, rank)

### 3. Emolument Details View
- [ ] **View Details Functionality**
  - [ ] Clicking "View Details" shows full emolument information
  - [ ] Can view all emolument fields:
    - [ ] Bank Name
    - [ ] Bank Account Number
    - [ ] Pension Fund Administrator (PFA) Name
    - [ ] Retirement Savings Account (RSA) PIN
    - [ ] Next of Kin information
  - [ ] Can see officer's basic information
  - [ ] Can see emolument submission date
  - [ ] Can see emolument status (should be "RAISED")

### 4. Emolument Assessment Process
- [ ] **Assessment Actions**
  - [ ] Can click "Assess" button for each emolument
  - [ ] Assessment form/interface is accessible
  - [ ] Can verify banking information accuracy
  - [ ] Can validate PFA details
  - [ ] Can validate RSA PIN format (PEN prefix + 12 digits)
  - [ ] Can confirm completeness of next of kin information
  - [ ] Can add comments/notes during assessment
  - [ ] Can approve assessment (status changes to "ASSESSED")
  - [ ] Can reject assessment if issues found
  - [ ] Assessment timestamp is recorded

### 5. Data Verification
- [ ] **Information Verification**
  - [ ] Bank Name is valid and complete
  - [ ] Bank Account Number format is correct
  - [ ] PFA Name is valid
  - [ ] RSA PIN follows format: PEN + 12 digits
  - [ ] Next of Kin information is complete (name, relationship, contact)
  - [ ] All required fields are present

### 6. Assessment Workflow
- [ ] **Workflow Progression**
  - [ ] Receives notification when officers raise emoluments
  - [ ] Can access emoluments in "RAISED" status
  - [ ] After assessment, status changes to "ASSESSED"
  - [ ] Assessed emoluments move to Validator for next step
  - [ ] Cannot assess already assessed emoluments
  - [ ] Cannot assess validated emoluments

### 7. Reporting & Tracking
- [ ] **Assessment Reports**
  - [ ] Can generate assessment reports
  - [ ] Can track assessment completion rates
  - [ ] Can see pending assessments count
  - [ ] Can see completed assessments count
  - [ ] Reports show command-level data only

### 8. Error Handling & Edge Cases
- [ ] **Error Scenarios**
  - [ ] Handles missing emolument data gracefully
  - [ ] Shows appropriate error messages for invalid data
  - [ ] Cannot assess emoluments outside active timeline
  - [ ] Cannot access emoluments from other commands
  - [ ] System prevents duplicate assessments

---

## COMPLETE SYSTEM SPECIFICATION TESTING CHECKLIST

### A. ROLES & PERMISSIONS

#### HRD (Human Resources Department)
- [ ] Generate Staff Orders
- [ ] Generate Movement Orders
- [ ] Onboard Serving Officers
- [ ] Generate Eligibility List for Promotion
- [ ] Generate Retirement List
- [ ] Nominate Officers for courses
- [ ] Create emolument timeline (Start and End date)
- [ ] Extend emolument timeline
- [ ] Trigger system to match criteria for Manning Level requests
- [ ] Alter Staff Orders and Movement Orders
- [ ] Set years in rank for promotion eligibility
- [ ] Create new types of leave and assign duration
- [ ] Onboard officers for NCS Employee App
- [ ] System-wide configuration and parameter management
- [ ] Generate comprehensive system reports

#### Staff Officer
- [ ] Prepare the Roaster
- [ ] Send Manning Level of officers to HRD (Rank, compulsory, Sex and Qualification optional)
- [ ] Minute pass request to the Comptroller or DC Admin
- [ ] Prepare Internal Staff Order
- [ ] Prepare a release letter
- [ ] Document an officer
- [ ] Send Manning Level Request to HRD
- [ ] Minute Leave or Pass requests to DC Admin for approval
- [ ] Print out approved leave or pass
- [ ] Add officers to Management Groups in NCS Employee App
- [ ] Prepare and manage duty rosters for the command
- [ ] Create manning level requests
- [ ] Process leave and pass applications from officers
- [ ] Maintain command nominal roll
- [ ] Manage command chat groups in NCS App
- [ ] Track officer movements in and out of command
- [ ] Generate command-level reports and statistics

#### Building Unit
- [ ] Quarter the Officers
- [ ] Enter Quartered status Yes or No option at Command level
- [ ] Allocate quarters to eligible officers
- [ ] Update quartered status (Yes/No) in the system
- [ ] Maintain quarters occupancy database
- [ ] Process quarter allocation requests
- [ ] Deallocate quarters when officers are posted
- [ ] Track quarters maintenance requirements
- [ ] Generate accommodation reports
- [ ] Manage waiting lists for quarters

#### Establishment
- [ ] Onboard new officers
- [ ] Allocate Service Number to new officers (sequential numbering)
- [ ] Maintain service number registry
- [ ] Process new recruit documentation
- [ ] Create initial officer records
- [ ] Coordinate recruitment exercises
- [ ] Process appointment letters
- [ ] Assign initial ranks to new officers
- [ ] Ensure unique identification for all officers

#### Accounts
- [ ] Generate list of officers that has been Validated
- [ ] Extract payment data: Bank Name, Account Number, PFA, RSA
- [ ] Generate list of deceased officers (from Welfare Unit)
- [ ] Process salary payments and remittances
- [ ] Calculate and process death benefits
- [ ] Process pension remittances
- [ ] Generate payment reports and reconciliations
- [ ] Maintain financial audit trails
- [ ] Handle payment exceptions and corrections
- [ ] Generate payroll and financial statistics

#### Board (Promotion Board)
- [ ] Change officers Rank to a new rank
- [ ] Review promotion eligibility lists generated by HRD
- [ ] Conduct promotion exercises
- [ ] Update promotion effective dates
- [ ] Maintain promotion history records
- [ ] Generate seniority lists
- [ ] Consider disciplinary records in promotion decisions
- [ ] Make promotion recommendations
- [ ] Track time in rank for all officers

#### Assessor (Current Role Being Tested)
- [ ] Click Access for the officer
- [ ] View list of subordinate officers who have raised emoluments
- [ ] Access detailed emolument information for review
- [ ] Verify accuracy of banking information
- [ ] Validate PFA and RSA PIN details
- [ ] Confirm completeness of next of kin information
- [ ] Perform assessment by clicking "Assess" for each reviewed emolument
- [ ] Flag discrepancies or issues
- [ ] Generate assessment reports
- [ ] Track assessment completion rates

#### Validator
- [ ] Validate officers that the Assessor has assessed
- [ ] Review emoluments that have been assessed
- [ ] Perform final validation checks
- [ ] Ensure regulatory compliance
- [ ] Cross-verify documentation
- [ ] Approve emoluments for accounts processing
- [ ] Quality control of assessed information
- [ ] Maintain validation logs

#### Officer
- [ ] Fill all required fields during onboarding
- [ ] Apply for Leave
- [ ] Apply for Pass
- [ ] Raise Emolument (once a year)
- [ ] Chat through the NCS Employee Application
- [ ] Change display picture
- [ ] Login to raise emolument during timeline period
- [ ] Edit emolument fields: Bank Name, Account Number, PFA, RSA PIN, Next of KIN
- [ ] Complete onboarding process
- [ ] Update editable emolument fields
- [ ] Apply for leave (maximum 2 times per year for annual leave)
- [ ] Apply for pass (maximum 5 days, only after exhausting annual leave)
- [ ] View personal service records and history
- [ ] Access NCS Employee App for communication
- [ ] Participate in command-specific chat rooms
- [ ] Update profile picture
- [ ] Upload and maintain personal documents (JPEG format)
- [ ] Track application statuses (leave, pass, emolument)
- [ ] Receive system notifications and alerts

#### Area Controller (Comptroller)
- [ ] Name appears on Leave or pass as the approver
- [ ] Approve Roaster generated by the Staff Officer
- [ ] Approve Manning Level to be sent to HRD
- [ ] Validate officers (required before Accounts can generate lists)
- [ ] Indicate that an officer is deceased (along with Staff Role)
- [ ] Validate emoluments after assessment
- [ ] Provide final approval for emolument processing
- [ ] Approve duty rosters submitted by Staff Officer
- [ ] Approve manning level requests before forwarding to HRD
- [ ] Monitor overall command performance
- [ ] Make strategic decisions for the command
- [ ] Oversee multiple units within area of responsibility
- [ ] Review and approve internal movements

#### DC Admin (Deputy Comptroller Administration)
- [ ] Approve Pass
- [ ] Approve Leave
- [ ] Review and approve/reject leave applications minuted by Staff Officer
- [ ] Review and approve/reject pass requests (ensure 5-day maximum)
- [ ] Process urgent leave requests
- [ ] Ensure compliance with leave and pass eligibility rules
- [ ] Support Area Controller in administrative functions
- [ ] Handle routine operational approvals
- [ ] Maintain approval audit trail

#### Welfare
- [ ] Validate when an officer is deceased
- [ ] Generate data on deceased officer with required fields:
  - [ ] SVC no
  - [ ] Rank
  - [ ] DOB
  - [ ] Next of Kin(s)
  - [ ] Bank Name
  - [ ] Account Number
  - [ ] Retirement Savings Account Administrator (RSA)
- [ ] Process welfare claims and benefits
- [ ] Coordinate with Accounts for benefit payments
- [ ] Maintain beneficiary records
- [ ] Handle compassionate cases
- [ ] Process emergency support requests
- [ ] Generate welfare reports

---

### B. WORKFLOWS

#### 1. Emolument Form Workflow
- [ ] **Timeline Creation Phase**
  - [ ] HRD creates timeline for Officers to raise emolument
  - [ ] System activates timeline and notifies all officers
  - [ ] Cron job monitors for automatic extensions
  - [ ] HRD can extend Start and End date

- [ ] **Officer Submission Phase**
  - [ ] Officers can login during active timeline
  - [ ] Officers view previous year's data for reference
  - [ ] Officers can edit fields: Bank Name, Account Number, PFA, RSA PIN, Next of KIN
  - [ ] Officers submit emolument (Status: RAISED)

- [ ] **Assessment Phase** (Assessor Role)
  - [ ] Assessor receives notification of raised emoluments
  - [ ] Assessor clicks Access for the officer
  - [ ] Officers appear on a list with button for view details
  - [ ] Assessor reviews only subordinate officers' submissions
  - [ ] Assessor verifies all information accuracy
  - [ ] Assessor approves assessment (Status: ASSESSED)

- [ ] **Validation Phase**
  - [ ] Validator/Area Controller receives assessed emoluments
  - [ ] Validator validates officers that the Assessor has assessed
  - [ ] Validator performs final review and compliance check
  - [ ] Area Controller validates the officers
  - [ ] Validator approves for payment (Status: VALIDATED)

- [ ] **Payment Phase**
  - [ ] Accounts generates list of validated officers
  - [ ] Accounts extracts payment information
  - [ ] Accounts processes payments (Status: PROCESSED)

#### 2. Officers Onboarding Workflow
- [ ] **For New Recruits**
  - [ ] Establishment allocates service number
  - [ ] Establishment creates initial record
  - [ ] System generates onboarding link
  - [ ] Email sent to new officer

- [ ] **For Existing Officers**
  - [ ] HRD initiates onboarding
  - [ ] HRD enters officer email
  - [ ] System sends unique onboarding link

- [ ] **Officer Completion**
  - [ ] Officer receives email and clicks link
  - [ ] Officer fills multi-step form (4 steps)
  - [ ] Officer accepts disclaimer about false information
  - [ ] Officer uploads documents (preferably JPEG)
  - [ ] System displays caveat about false information
  - [ ] System creates account and assigns to command chat
  - [ ] Establishment processes the onboarding

#### 3. Pass and Leave Workflow
- [ ] **Leave Application Process**
  - [ ] Officer checks leave eligibility in system
  - [ ] Officer applies for Leave or Pass
  - [ ] Officer selects from 28 leave types
  - [ ] Officer submits application with dates and reason
  - [ ] System calculates days and validates eligibility
  - [ ] Staff Officer receives leave application
  - [ ] Staff Officer reviews supporting documents
  - [ ] Staff Officer minutes it to the DC Admin for approval
  - [ ] Application status changes to MINUTED
  - [ ] DC Admin reviews minuted application
  - [ ] DC Admin approves or rejects with reason
  - [ ] If approved, Area Controller's name appears on document
  - [ ] Status changes to APPROVED
  - [ ] Staff Officer prints approved leave document
  - [ ] Staff Officer distributes to officer
  - [ ] System sends automatic notification to officer
  - [ ] System monitors leave (alerts 72 hours before end)

- [ ] **Pass Application Process**
  - [ ] Maximum 5 days only
  - [ ] Annual leave must be exhausted
  - [ ] Maximum 2 passes per year
  - [ ] Officer applies for pass (system validates eligibility)
  - [ ] Staff Officer processes and minutes to DC Admin
  - [ ] DC Admin approves (ensures 5-day limit)
  - [ ] Staff Officer prints and distributes
  - [ ] System monitors pass duration
  - [ ] Automatic alert sent on expiry date

#### 4. Manning Level Workflow
- [ ] **Request Creation**
  - [ ] Staff Officer analyzes command needs
  - [ ] Staff Officer prepares list (Rank, Sex and Qualification optional)
  - [ ] Staff Officer enters requirements (e.g., DC needed 3, AC needed 3)
  - [ ] Staff Officer submits to Area Controller

- [ ] **Approval Process**
  - [ ] Staff Officer minutes it for the Comptroller's approval
  - [ ] Area Controller reviews justification
  - [ ] Area Controller approves and forwards to HRD

- [ ] **Matching Process**
  - [ ] HRD receives request
  - [ ] System searches for matching officers
  - [ ] System filters by rank, sex, qualification, status
  - [ ] HRD triggers the system to match the criteria
  - [ ] HRD selects from matched candidates
  - [ ] HRD generates movement orders
  - [ ] HRD can alter the Staff Orders and Movement Orders

- [ ] **Implementation**
  - [ ] Officers receive posting notifications
  - [ ] New command Staff Officer sees officer on dashboard
  - [ ] Staff Officer documents arrival
  - [ ] System updates nominal rolls and chat rooms

#### 5. Staff Order and Movement Order Workflow
- [ ] **Staff Order**
  - [ ] HRD searches for the officer
  - [ ] HRD changes posting at any time
  - [ ] Officer appears on dashboard of new command's Staff Officer
  - [ ] Staff Officer documents the officer
  - [ ] Officer's name automatically goes off previous command's nominal roll
  - [ ] Officer goes to new command chat room and leaves previous one

- [ ] **Movement Order**
  - [ ] HRD enters criteria (time in Months)
  - [ ] HRD posts the officers
  - [ ] HRD can use Manning Level for postings
  - [ ] Officer appears on dashboard of new command's Staff Officer
  - [ ] Staff Officer documents the officer
  - [ ] Officer's name automatically goes off previous command's nominal roll
  - [ ] Officer goes to new command chat room and leaves previous one

#### 6. Building/Quartering Workflow
- [ ] Building Unit enters Quartered status Yes or No option at Command level
- [ ] Status on PE form indicates whether the Officer is quartered or not

#### 7. Eligibility List for Promotion Workflow
- [ ] **Setup Phase**
  - [ ] HRD sets number of years officer will stay on rank to be eligible
  - [ ] System stores promotion criteria

- [ ] **Generation Phase**
  - [ ] System automatically checks eligibility
  - [ ] System checks exclusions:
    - [ ] Officers not meeting time in rank
    - [ ] Interdicted officers
    - [ ] Suspended officers
    - [ ] Dismissed officers
    - [ ] Officers under investigation
    - [ ] Officers with pending investigation issues
    - [ ] Deceased officers
  - [ ] Excluded officers don't feature on Eligibility List
  - [ ] System generates eligibility list with required fields

- [ ] **Board Review**
  - [ ] Board receives eligibility list
  - [ ] Board conducts promotion exercise
  - [ ] Board makes promotion decisions
  - [ ] Board updates ranks in system

- [ ] **Implementation**
  - [ ] System updates officer records
  - [ ] New rank and date recorded
  - [ ] Officers receive promotion notifications

#### 8. Statutory Retirement List Workflow
- [ ] **Retirement Criteria**
  - [ ] Officer due for retirement at age 60 years
  - [ ] Officer due for retirement at 35 years in service
  - [ ] Whichever comes first

- [ ] **Automatic Detection**
  - [ ] System daily checks for officers reaching age 60
  - [ ] System daily checks for officers completing 35 years service
  - [ ] System generates retirement list

- [ ] **Processing**
  - [ ] HRD reviews retirement list
  - [ ] HRD generates list with required fields
  - [ ] System calculates pre-retirement leave (3 months before)
  - [ ] System activates pre-retirement status
  - [ ] Notifications sent to retiring officer, Accounts, and Welfare
  - [ ] Officer is informed

#### 9. Service Number Allocation Workflow
- [ ] Establishment allocates service number
- [ ] Sequential numbering (last service number + 1)
- [ ] Pattern precedence for new recruitment

#### 10. NCS Employee App (Social Media Application) Workflow
- [ ] **Automatic Room Assignment**
  - [ ] Onboarding: Officer added to command chat room
  - [ ] Command chat rooms only include officers of that command
  - [ ] On posting: Officer moved from old to new command chat
  - [ ] Automatic transfer when officer is posted out
  - [ ] Management groups created for AC and above
  - [ ] Staff Officer can manually add officers to groups

- [ ] **Management Groups**
  - [ ] Automatically created in a command from Assistant Comptroller and above
  - [ ] Staff Officer can add officers to unit
  - [ ] Unit heads below Chief Superintendent can be added
  - [ ] Legal department non-officers can be added
  - [ ] Non-officers must be onboarded by HRD

---

### C. BUSINESS RULES & CONSTRAINTS

#### General Rules
- [ ] Document Upload Format: JPEG preferred to save space
- [ ] Onboarding Caveat: False information can lead to Dismissal for Forgery
- [ ] Pass Rules:
  - [ ] Maximum 5 days
  - [ ] Can only be applied if Annual leave is exhausted
- [ ] Annual Leave Rules:
  - [ ] Maximum 2 times per year
  - [ ] Duration: 28 Days for GL 07 and Below, 30 days for Level 08 and above
- [ ] Emolument Rules:
  - [ ] Done once a year
  - [ ] HRD creates timeline with Start and End date
  - [ ] Timeline can be extended by HRD or Cron Job
  - [ ] Officers can only login during timeline period
- [ ] RSA PIN Format:
  - [ ] Usually has prefix PEN
  - [ ] Usually 12 digits

#### Retirement Rules
- [ ] Retirement Criteria: Age 60 years OR 35 years in service (whichever comes first)
- [ ] Pre-Retirement Leave: 3 months before retirement date

#### Promotion Rules
- [ ] Eligibility Exclusions:
  - [ ] Interdicted officers
  - [ ] Suspended officers
  - [ ] Dismissed officers
  - [ ] Officers with pending investigation issues
  - [ ] Deceased officers
- [ ] Rank Changes: Done by Board only, Officers cannot edit their own rank

#### Validation Rules
- [ ] Emolument Validation Flow:
  - [ ] Assessor accesses officers under his command who have raised Emolument
  - [ ] Validator validates officers that the Assessor has assessed
  - [ ] Area Controller validates officers (required before Accounts can generate lists)
- [ ] Deceased Officer Validation:
  - [ ] Area Controller or Staff Role can indicate deceased
  - [ ] Welfare validates when an officer is deceased
  - [ ] Welfare generates data on deceased officers

#### Service Number Rules
- [ ] Allocation: Done by Establishment unit
- [ ] Sequential numbering: New officers start from last service number + 1
- [ ] Example: If last is 57616, new will be 57617, 57618, etc.

#### Posting and Transfer Rules
- [ ] Automatic Updates:
  - [ ] Officer name automatically goes off previous command's nominal roll
  - [ ] Officer appears on new command's Staff Officer dashboard
  - [ ] Officer automatically transfers to new command chat room
- [ ] Staff Orders: HRD can search and change posting at any time
- [ ] Movement Orders: HRD enters criteria based on time spent (in Months)

#### Notification Rules
- [ ] Pass Notifications: System alerts officer when pass ends
- [ ] Leave Notifications: System alerts officer 72 hours before leave ends
- [ ] Approval Notifications: Officer receives automatic message after approval

#### NCS Employee App Rules
- [ ] Chat Rooms: Command chat rooms only include officers of that command
- [ ] Automatic transfer when officer is posted
- [ ] Management Groups: Automatically created for AC and above
- [ ] Staff Officer can add officers to unit

#### Leave Type Management
- [ ] HRD can create new types of leave and assign duration
- [ ] 28 standard leave types available

---

### D. LEAVE TYPES (28 Types)

- [ ] Annual Leave (28 Days for GL 07 and Below, 30 days for Level 08 and above, max 2 times per year)
- [ ] Leave on Permanent invalidation (2 months, recommended by Medical Officer)
- [ ] Deferred Leave
- [ ] Casual Leave (7 working days, must exhaust Annual leave first)
- [ ] Sick Leave (recommended by medical officer)
- [ ] Maternity Leave (112 working days a year, with EDD field)
- [ ] Maternity Leave on adoption of Child Under 4 months (84 working days a year)
- [ ] Paternity Leave (14 working days a year)
- [ ] Paternity Leave on adoption of Child Under 4 months (14 working days a year)
- [ ] Examination Leave
- [ ] Leave for Compulsory Examination
- [ ] Leave for non compulsory examination
- [ ] Sabbatical Leave (12 calendar months once every 5 years)
- [ ] Study Leave without pay (4 years in first instance, can extend)
- [ ] Study Leave with pay (3 years with extension of 1 year)
- [ ] Leave on compassionate grounds
- [ ] Pre-retirement leave
- [ ] Leave of absence
- [ ] Leave on grounds on urgent private affairs
- [ ] Leave for cultural and sporting activities
- [ ] Leave to take part in trade Union activities
- [ ] Leave for in-service training (4 years maximum)
- [ ] Leave of absence to Join Spouse on course of instruction abroad (9 months)
- [ ] Special leave to join spouse on ground of public policy
- [ ] Leave of absence on grounds of public policy for technical aid program
- [ ] Leave of absence for Special or Personal assistants on ground of public policy
- [ ] Leave of absence for Spouse of President, Vice President, Governor and Deputy Governor
- [ ] HRD can create custom leave types

---

### E. SYSTEM SAFEGUARDS

- [ ] **Role Separation**
  - [ ] No user can approve their own requests
  - [ ] Assessors cannot validate
  - [ ] Staff Officers cannot approve
  - [ ] Clear hierarchical structure

- [ ] **Data Integrity**
  - [ ] Automatic backups every 6 hours
  - [ ] Transaction logs for all changes
  - [ ] Rollback capabilities
  - [ ] Audit trail for 7 years

- [ ] **Access Control**
  - [ ] Role-based permissions
  - [ ] Command-level restrictions
  - [ ] Personal data protection
  - [ ] Secure authentication

- [ ] **Process Validation**
  - [ ] Eligibility checks before applications
  - [ ] Status verification before transitions
  - [ ] Duplicate prevention mechanisms
  - [ ] Error handling at each stage

---

### F. PERFORMANCE INDICATORS

- [ ] **System Efficiency Metrics**
  - [ ] Emolument processing time: Target 5 days from raise to validation
  - [ ] Leave approval cycle: Target 48 hours
  - [ ] Manning request fulfillment: Target 7 days
  - [ ] Movement order implementation: Target 72 hours
  - [ ] Retirement processing: 30 days advance notification

- [ ] **User Satisfaction Indicators**
  - [ ] Self-service capabilities for officers
  - [ ] Real-time status tracking
  - [ ] Automated notifications
  - [ ] Mobile app accessibility
  - [ ] Document upload simplicity

- [ ] **Administrative Efficiency**
  - [ ] Reduced manual data entry
  - [ ] Automated report generation
  - [ ] Bulk processing capabilities
  - [ ] Integration between modules
  - [ ] Centralized dashboard for each role

---

### G. CRITICAL INTERACTION POINTS

- [ ] **Emolument Chain of Command**
  - [ ] Officer (Raises) → Assessor (Reviews) → Validator (Approves) → Accounts (Pays)

- [ ] **Leave Approval Chain**
  - [ ] Officer (Applies) → Staff Officer (Minutes) → DC Admin (Approves) → Staff Officer (Distributes)

- [ ] **Manning Request Chain**
  - [ ] Staff Officer (Requests) → Area Controller (Approves) → HRD (Processes) → Officers (Posted)

- [ ] **Posting Implementation Chain**
  - [ ] HRD (Initiates) → Officers (Notified) → Staff Officer (Documents) → System (Updates Rolls)

- [ ] **Deceased Benefits Chain**
  - [ ] Reporter (Indicates) → Welfare (Validates) → Accounts (Calculates) → Next of Kin (Receives)

---

## TESTING NOTES

### For Assessor Role Specifically:
1. Focus on emolument assessment workflow
2. Verify command-level access restrictions
3. Test assessment approval/rejection functionality
4. Verify data validation (banking, PFA, RSA PIN, Next of Kin)
5. Test notification system for new emoluments
6. Verify status transitions (RAISED → ASSESSED)
7. Test reporting and tracking features

### General Testing Approach:
1. Test each role's core functions systematically
2. Verify workflow progression through all stages
3. Test business rules and constraints
4. Verify access control and permissions
5. Test error handling and edge cases
6. Verify notifications and alerts
7. Test system safeguards and data integrity
8. Verify performance indicators are met

---

## TESTING STATUS SUMMARY

**Total Specifications to Test:** [Count as you complete]  
**Completed:** [ ]  
**In Progress:** [ ]  
**Pending:** [ ]  
**Issues Found:** [Document separately]

---

*Last Updated: [Current Date]*  
*Testing Role: Assessor*  
*Next Role to Test: [Specify]*

