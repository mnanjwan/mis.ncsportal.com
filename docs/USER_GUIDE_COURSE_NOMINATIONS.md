# User Guide: Course Nominations (Officers on Course)

**Direct, step-by-step guide. Nothing omitted.**

---

## Who Can Do What

| Role   | Can do |
|--------|--------|
| **HRD** | View all course nominations, nominate officers, edit (in-progress only), mark complete, delete (in-progress only), print. |
| **Officer** | View their own course nominations; **upload certificate of completion** for Pending courses (then nomination goes for review). |

Only HRD can put officers on course. Officers cannot nominate themselves or others.

---

## HRD: Where to Go

- **Menu:** HRD → **Course Nominations** (or sidebar “Course Nominations”).
- **URL:** `/hrd/courses`

---

# PART 1 — HRD TASKS

---

## 1. View Course Nominations List

1. Log in as **HRD**.
2. Go to **HRD** → **Course Nominations**.
3. You see a table: Officer, Course Name, Course Type, Start Date, Status (In Progress / Completed), Actions.

**Tabs**

- **All** — Every nomination.
- **In Progress** — Not yet completed.
- **Completed** — Marked complete (permanent record).

**Sorting**

- Click column headers (Officer, Course Name, Course Type, Start Date, Status) to sort. Click again to toggle ascending/descending.

**Actions per row**

- **View** — Open nomination details (always).
- **Edit** (pencil) — Only for **In Progress**.
- **Delete** (trash) — Only for **In Progress**.

**Pagination**

- Use the pagination at the bottom to move between pages (20 per page).

---

## 2. Nominate Officers for a Course (Put Officers on Course)

1. On Course Nominations, click **Nominate Officers** (top right).
2. You are on **Nominate Officer for Course**.

**Step 1 — Select officers (required)**

- In **Officers**, type in the search box (name or service number).
- A dropdown lists matching officers. **Tick the checkbox** for each officer you want to nominate (you can select more than one).
- Selected officers appear below; you can remove one with the X.
- You must select **at least one** officer.

**Step 2 — Course name (required)**

- Click **Course Name** (“Select a course or enter new...”).
- Either:
  - **Pick an existing course** from the list (use the search if needed), or
  - **Choose “+ Add New Course”** and type the course name in the text box that appears.
- If you choose “+ Add New Course”, you must fill in the new course name.

**Step 3 — Other fields**

- **Course Type** — Optional (e.g. “Professional Development”, “Technical Training”).
- **Start Date** — **Required.** Pick the date.
- **End Date** — Optional. If set, must be on or after Start Date.
- **Notes** — Optional.

**Step 4 — Submit**

- Click **Nominate Officers**.
- If validation fails (e.g. no officer selected, no course name), errors appear; fix and submit again.
- On success you are redirected to the Course Nominations list and see a success message (e.g. “X officers nominated for course successfully!”).

**What happens in the system**

- One course nomination record is created **per selected officer** for that course and dates.
- Each nominated officer receives an **in-app notification** and an **email** (if they have an email) that they have been nominated for the course.
- If you used “+ Add New Course”, that name is added to the master course list for future use.

---

## 3. View a Single Course Nomination

1. On Course Nominations list, click **View** on the row.
2. You see: Course name, status (In Progress / Completed), Officer (name and service number), Course Type, Start Date, End Date, Nominated By, Notes, and if completed: Completion Date, Certificate URL (if any).
3. If the nomination is **In Progress**, you also see an **Edit** button and a **Mark Course as Completed** section (see below).
4. **Back to Courses** returns you to the list.

---

## 4. Edit a Course Nomination

- Allowed **only** when status is **In Progress**. Completed nominations cannot be edited.

**From the list**

1. Click the **Edit** (pencil) button on the row.

**From the detail page**

1. Open the nomination with **View**, then click **Edit**.

**On the Edit form**

- **Officer** — Required. Search and select one officer (single selection).
- **Course Name** — Required. Select from list or use “+ Add New Course” and type name.
- **Course Type** — Optional.
- **Start Date** — Required.
- **End Date** — Optional (on or after Start Date).
- **Notes** — Optional.

Click **Update**. You are redirected to the nomination detail page with a success message.

---

## 5. Mark a Course as Completed

- Only for nominations that are **In Progress**. Once completed, the course is part of the officer’s permanent record and **cannot be edited or deleted**.

1. Open the nomination (**View**).
2. Scroll to **Mark Course as Completed**.
3. Fill:
   - **Completion Date** — **Required.** Must be on or after the nomination’s Start Date.
   - **Certificate URL** — Optional (link to certificate).
   - **Completion Notes** — Optional (replaces or adds to existing notes).
4. Click **Mark as Completed**.

**Result**

- Status becomes **Completed**. Completion date (and optional certificate/notes) is saved.
- The officer gets an in-app notification (and email if they have one) that the course has been marked completed and recorded in their service record.
- Edit and Delete are no longer available for this nomination.

---

## 6. Delete a Course Nomination

- Allowed **only** when status is **In Progress**. Completed nominations **cannot** be deleted.

1. On the list, click the **Delete** (trash) button on the row.
2. A modal asks for confirmation and shows the course name.
3. Click **Delete** to confirm, or **Cancel** to keep it.

After deletion, the nomination is removed and no longer appears in the list or on the officer’s course nominations.

---

## 7. Print Course Nominations

1. On Course Nominations, click **Print** (top right).
2. In the **Print Course Nominations** modal:
   - **Course** — **Required.** Search/select:
     - **All Courses** — Print all nominations (filtered by status only), or
     - A **specific course name** — Print only that course’s nominations.
   - **Status** — Choose **All**, **In Progress**, or **Completed**.
3. Click **Print**.

A new tab/window opens with the print view (grouped by course, with officer list: serial number, service number, rank, name). Use the browser’s Print (e.g. Ctrl+P / Cmd+P) to print or save as PDF.

---

# PART 2 — OFFICER TASKS

---

## 8. Officer: View Your Course Nominations

1. Log in as an **Officer** (user linked to an officer record).
2. Go to **Course Nominations** (sidebar or dashboard link).
3. You see **only your own** course nominations. You cannot add, edit, or delete.

**Filters**

- **Status** — All Statuses / Pending / Completed.
- **Year** — All Years or a specific year (from start dates of your nominations).
- **Sort By** — Start Date, Course Name, Completion Date, or Nominated Date.
- **Sort Order** — Ascending / Descending.

Apply filters and click/search as needed; the list updates.

**What you see per nomination**

- Course name, course type, start date, end date, status (Pending / **Pending review** / Completed), completion date (if completed), who nominated you (if they have an officer record, their name is shown).
- **Actions:** For **Pending** courses, an **“Upload certificate”** button is shown. Use it to submit your certificate or proof of completion (PDF or image, max 10 MB). You can upload more than one file if needed.

**Uploading your certificate of completion**

1. For a **Pending** course, click **Upload certificate** in the Actions column (or on mobile, the button under the course card).
2. In the modal, choose a file (PDF, JPG, or PNG, max 10 MB) and click **Upload**.
3. Your nomination is then **submitted for review**: status becomes **Pending review**, **HRD and your command’s Staff Officer are notified** (in-app and by email if configured), and they can see your document(s) when they open the nomination. They will review and formally **mark the course as completed** when satisfied. Until then, the course remains “Pending review” on your side.

**Status meanings (officer)**

- **Pending** — You have not yet submitted proof of completion.
- **Pending review** — You have uploaded certificate(s); HRD/Staff Officer will review and mark the course complete.
- **Completed** — The course has been marked complete and is recorded in your service record.

**Notifications**

- When HRD nominates you, you get an in-app notification and an email (if your account has an email).
- When HRD marks the course completed, you get a notification (and email) that it has been recorded in your service record.

---

# QUICK REFERENCE

| Action | Who | Where |
|--------|-----|--------|
| See all nominations | HRD | HRD → Course Nominations |
| Put officers on course | HRD | Course Nominations → Nominate Officers |
| View one nomination | HRD | List → View |
| Edit nomination | HRD (In Progress only) | List → Edit, or View → Edit |
| Mark complete | HRD (In Progress only) | View nomination → Mark Course as Completed |
| Delete nomination | HRD (In Progress only) | List → Delete (trash) |
| Print | HRD / Staff Officer | Course Nominations → Print |
| See my courses | Officer | Course Nominations (own list only) |
| Upload certificate (proof of completion) | Officer | Course Nominations → Upload certificate (for Pending courses) |
| Review officer uploads and mark complete | HRD / Staff Officer | View nomination → see “Officer-submitted completion documents” → Mark as Completed |

---

# RULES (Do Not Miss)

1. **HRD and Staff Officer** can create, edit, mark complete, or delete course nominations. Staff Officer only sees and can act on officers in their command. Officers can **upload certificate of completion** for their own Pending courses; then the nomination goes **for review** (Pending review) until HRD/Staff Officer marks it complete.
2. **At least one officer** must be selected when nominating; **course name** and **start date** are required.
3. **End date** is optional; if set, it must be on or after start date. **Completion date** (when marking complete) must be on or after start date.
4. **Completed** nominations are permanent: no edit, no delete. Only “Mark complete” and “View” apply.
5. **In Progress** nominations can be edited (officer, course, dates, notes) or deleted.
6. Each time you nominate, **one record per selected officer** is created; each of those officers is notified (in-app + email if available).
7. **New course names** (via “+ Add New Course”) are stored in the master course list and can be chosen later for other nominations.
8. **Print** requires choosing a course (All Courses or a specific course) and a status (All / In Progress / Completed).

---

*End of user guide.*
