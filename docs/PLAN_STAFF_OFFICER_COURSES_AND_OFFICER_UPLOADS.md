# Plan: Staff Officer Course Nominations (Command-Scoped) + Officer Completion Uploads

## Summary

1. **Staff Officer** can do everything HRD does for course nominations, but **only for officers in their command** (same list, nominate, view, edit, mark complete, delete, print — all scoped by command).
2. **Officers** keep their current role: view own nominations, get notifications. **New:** Officers can **upload documents** to prove course completion for in-progress nominations; HRD/Staff Officer see these when marking complete.
3. **Officer completion uploads:** This flow does **not** fully exist today. Document categories `course_completed` and `training_certificate` exist, but there is no link from a document to a specific course nomination, and no officer UI to “upload proof for this course”. So we add that.

---

## Part A — Staff Officer: Course Nominations (Command-Only)

### A.1 Behaviour

- **Who:** Users with role **Staff Officer** (in addition to **HRD**).
- **Scope:** Staff Officer sees and can act only on officers whose **present_station** equals the **command_id** of their Staff Officer role (from the role pivot). HRD continues to see all officers and all nominations.
- **Actions (same as HRD, but filtered):**
  - List course nominations (only where officer is in their command).
  - Nominate officers for course (only officers in their command).
  - View / Edit / Mark complete / Delete (only if the nomination’s officer is in their command).
  - Print (only nominations for officers in their command).
- **Officer experience:** Unchanged. Officers still see their own nominations and get the same notifications; they don’t care whether HRD or Staff Officer did the action.

### A.2 Implementation Approach

**Option (recommended): Shared routes, role-based scoping**

- Use the **same** course routes (e.g. under `hrd` or a shared prefix) and the same controller (or a thin base + one controller).
- **Middleware:** Allow both HRD and Staff Officer, e.g. `role:HRD|Staff Officer`.
- **Resolve command for Staff Officer:** Same pattern as `QueryController` / `PassApplicationController`:
  - Get Staff Officer’s command:  
    `$user->roles()->where('name', 'Staff Officer')->wherePivot('is_active', true)->first()`  
    then `$commandId = $pivot->command_id`.
  - Officers in command: `Officer::where('present_station', $commandId)`.
- **Scoping in controller:**
  - **List:** If Staff Officer → `OfficerCourse::whereIn('officer_id', $officersInCommandIds)`. If HRD → no filter.
  - **Create (form):** Officers dropdown: if Staff Officer → only `Officer::where('present_station', $commandId)`; if HRD → all (current behaviour).
  - **Store:** Validate that every `officer_id` is in the allowed set (for Staff Officer: in command).
  - **Show / Edit / Update / Mark complete / Destroy:** Resolve the `OfficerCourse`; if user is Staff Officer, check `$course->officer->present_station === $commandId`; else 403 or redirect.
  - **Print:** Same filter: only nominations for officers in command when user is Staff Officer.
- **Views:** Reuse existing HRD views. No need for separate Staff Officer course views unless you want a different menu entry/label.
- **Routes:** Either:
  - Keep routes under `hrd` and allow Staff Officer access to the same URLs, or
  - Add a `staff-officer/courses` prefix that uses the same controller with the same scoping (so Staff Officer has a clear entry point). Both can point to the same controller methods.

### A.3 Navigation

- **Staff Officer sidebar:** Add a **“Course Nominations”** (or “Courses”) link, e.g. under **Personnel Management** or **Other**, pointing to the same list (with command filter applied). So Staff Officer has one place to open course nominations, same as HRD but scoped.

### A.4 Edge Cases

- If a Staff Officer user has no `command_id` on their Staff Officer role → redirect or error: “Command not found for Staff Officer role.” (same as Queries).
- If an officer is moved to another command after a nomination was created, the nomination stays; the **original** Staff Officer (whose command the officer was in when nominated) can still manage it if you consider “created while in my command” as the rule, or you can restrict to “officer’s current command only” so only the current command’s Staff Officer can manage it. **Recommendation:** Scope by **current** `present_station` so that only the Staff Officer of the officer’s current command can manage their nominations (simpler and consistent with “command-only” meaning “officers in your command right now”).

---

## Part B — Officer Uploads for Course Completion

### B.1 Current State

- **Exists:**  
  - `OfficerCourse`: has `certificate_url` (optional), set by HRD/Staff Officer when marking complete.  
  - `OfficerDocument`: `officer_id`, `document_type`, file storage; used for general officer documents (onboarding, HRD uploads).  
  - Config: `document_categories` includes `course_completed` and `training_certificate`.
- **Does not exist:**  
  - No link between a document and a **specific** course nomination (`OfficerCourse`).  
  - No officer UI to upload a document and attach it to a given in-progress course nomination.  
  - So “officer uploads documents to show he has completed the course” is **not** implemented yet.

### B.2 Desired Flow

1. Officer has an **in-progress** course nomination.
2. Officer completes the course externally and obtains proof (certificate, transcript, etc.).
3. Officer goes to **Course Nominations**, selects the in-progress course, and **uploads one or more documents** (e.g. certificate, completion letter) as proof of completion.
4. HRD or Staff Officer (whoever can see that nomination) opens the nomination, sees the officer’s uploaded documents, and **marks the course as completed** (completion date, optional certificate URL/notes). The uploaded documents are the evidence; optionally one of them can be set as the “certificate” link (`certificate_url`) or the first upload can be used.

So: **Officer uploads proof → HRD/Staff Officer reviews and marks complete.** Officer does not mark the course complete themselves.

### B.3 Implementation Options

**Option 1 — Link existing OfficerDocument to OfficerCourse (recommended)**

- Add to `officer_documents` (migration):
  - `officer_course_id` (nullable, foreign key to `officer_courses`).
- When an officer uploads “completion proof” for a nomination, create an `OfficerDocument` with:
  - `officer_id` = current officer,
  - `document_type` = e.g. `course_completed` or `training_certificate`,
  - `officer_course_id` = the nomination id.
- **Officer UI (Course Nominations):** For each **in-progress** nomination, show an **“Upload completion document”** (or “Submit proof of completion”) control: choose file(s), upload. Backend creates `OfficerDocument` rows with `officer_course_id` set. Only allow for the logged-in officer’s own nominations.
- **HRD/Staff Officer “Mark complete” view:** When showing the “Mark Course as Completed” form, load and display documents where `officer_course_id` = this nomination. Show links to download. Optionally allow selecting one as “certificate” (and set `OfficerCourse.certificate_url` to that file’s URL or path) or keep `certificate_url` as a separate manual field.
- **Reuse:** Same storage and categories; minimal new tables; HRD uploads/browse remain unchanged.

**Option 2 — New table `officer_course_documents`**

- New table: `officer_course_id`, `file_path`, `file_name`, `uploaded_by`, timestamps.
- Officer uploads go only here. “Mark complete” page lists these.
- Pros: Clear separation. Cons: Duplicated file-handling logic and two places for “officer documents”; config categories not used for this flow.

**Recommendation:** Option 1 (nullable `officer_course_id` on `officer_documents`).

### B.4 Access Control

- **Upload:** Only the officer who owns the nomination can upload (check `OfficerCourse.officer_id` === current user’s officer id, and nomination is in progress).
- **View uploads:** HRD can see all; Staff Officer can see uploads for nominations they can access (same command scoping as Part A).
- **Mark complete:** Unchanged (HRD or Staff Officer only).

### B.5 Officer UX Summary

- **Course Nominations** page: For each **In Progress** course, show:
  - Existing info (course name, dates, etc.).
  - **“Upload completion document”** (or “Submit proof”): button/modal to attach file(s). After upload, show “Uploaded: [filename(s)]” and allow further uploads if needed.
- No “Mark as complete” button for the officer; completion is always done by HRD/Staff Officer after reviewing uploads.

---

## Part C — What Stays the Same

- **Officer role:** View own nominations; filter by status/year/sort; receive notifications (nominated, completed). No change except new upload capability for in-progress courses.
- **Notifications:** Keep current behaviour: notify officer when nominated and when course is marked completed.
- **Completed nominations:** Still permanent (no edit/delete). Completion is still done only by HRD/Staff Officer.
- **HRD:** Continues to see all commands and all nominations; no reduction in access.

---

## Implementation Order

1. **Staff Officer scoping (Part A)**  
   - Middleware: allow `Staff Officer` on course routes.  
   - In controller: resolve command for Staff Officer; restrict list/create/store/show/edit/update/complete/destroy/print to officers in that command.  
   - Sidebar: add “Course Nominations” for Staff Officer.  
   - Test: Staff Officer sees only command officers and their nominations; HRD still sees all.

2. **Officer completion uploads (Part B)**  
   - Migration: add `officer_course_id` (nullable) to `officer_documents`.  
   - Officer: on Course Nominations, for each in-progress nomination, add “Upload completion document” and store with `officer_course_id` and appropriate `document_type`.  
   - Mark-complete view: load and show officer uploads for that nomination; optionally use one as certificate or leave `certificate_url` manual.  
   - Access control: officer can only upload for own in-progress nominations; Staff Officer sees uploads only for their command’s nominations.

3. **Docs and user guide**  
   - Update `USER_GUIDE_COURSE_NOMINATIONS.md`: Staff Officer (command-only), officer upload completion documents, who marks complete.

---

## Short Answers to Your Questions

- **Can Staff Officer handle course nominations?**  
  Yes. Plan: same actions as HRD but **for officers in their command only** (using `present_station` = Staff Officer’s `command_id`).

- **Officer keeps playing his role as usual?**  
  Yes. Officers still only view their nominations and get notifications; the only addition is uploading completion documents for in-progress courses.

- **Officer uploads documents to show he has completed the course — does this exist?**  
  Not yet. Document categories exist (`course_completed`, `training_certificate`), but there is no link from a document to a specific nomination and no officer UI for it. Plan: add `officer_course_id` to `officer_documents`, add officer “Upload completion document” for in-progress nominations, and show those uploads to HRD/Staff Officer when they mark the course complete.
