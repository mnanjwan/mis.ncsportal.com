# Fleet: Vehicle & Officer Visibility by Role

## Product requirement (prompt)

**Head of Unit (Area Controller)**  
- Can see **fleet vehicles and the officers assigned to them only for his/her command** (command-scoped).

**CGC**  
- Can see **all fleet vehicles**, **the officers with each vehicle**, and **the command** for each vehicle/officer **across all commands in the system** (system-wide view).

---

## App terminology

| Term | Meaning in app |
|------|----------------|
| **Head of Unit** | **Area Controller** (T&L role: “Unit Head”) |
| **CD** | Chief Driver (T&L role) |
| **CGC** | Comptroller-General level (T&L approval role) |
| **Command** | Organisation unit; `FleetVehicle.current_command_id` / `Command` model; officers have `present_station` = command |
| **Officer with vehicle** | `FleetVehicle.current_officer_id` / `currentOfficer`; or current active `FleetVehicleAssignment.assigned_to_officer_id` |

---

## Visibility matrix

| Role | Scope of vehicles | Show officer? | Show command? |
|------|-------------------|---------------|---------------|
| **Area Controller** (Head of Unit) | **His/her command only** | Yes | No (all rows are same command) |
| **CD** (Chief Driver) | His/her command only (existing behaviour) | Yes | No |
| **CGC** | **All commands (system-wide)** | Yes | Yes |

Other T&L roles (CC T&L, DCG FATS, ACG TS, O/C T&L, Transport Store/Receiver) keep existing behaviour unless explicitly changed.

---

## Implementation checklist

1. **Access**
   - Allow **Area Controller** and **CGC** to open Fleet Vehicles list and vehicle detail:
     - Add `Area Controller` and `CGC` to `fleet.vehicles.index` and `fleet.vehicles.show` route middleware.
   - **Scope in controller** (`FleetVehicleController@index`):
     - **Area Controller**: filter vehicles by the user’s assigned command (e.g. `role_user.command_id` or equivalent). Show only vehicles where `current_command_id` = Area Controller’s command.
     - **CGC**: no command filter; show all vehicles across all commands (system-wide).
     - **CD**: keep existing behaviour (command-scoped).

2. **Fleet vehicles list** (`fleet/vehicles/index`)
   - For roles that “see officers”: add an **Officer** column (e.g. current officer name/service number or “—”) using `currentOfficer` (already loaded).
   - For **CGC**: add a **Command** column using `currentCommand` (already loaded).
   - Optionally show Officer for all roles that have access to the list; show Command only when user has role CGC (e.g. `@if(auth()->user()->hasRole('CGC'))`).

3. **Vehicle detail** (`fleet/vehicles/show`)
   - Already has `currentCommand` and `currentOfficer`; ensure Area Controller and CGC can open this page (route middleware). No change needed for layout unless you want CGC-specific emphasis on command.

4. **Sidebar / navigation**
   - **Area Controller**: add a “Vehicles” (or “Fleet vehicles”) link under Fleet pointing to `fleet.vehicles.index`.
   - **CGC**: add a “Vehicles” (or “Fleet vehicles”) link pointing to `fleet.vehicles.index` (e.g. next to “Fleet Requests”).

5. **Optional**
   - Export or report for CGC: all vehicles with officer and command columns (system-wide).
   - Filters on the vehicles list (by command, lifecycle status) for CGC.

6. **CC T&L direct allocation to command** (implemented)
   - CC T&L can allocate a vehicle (IN_STOCK, not reserved) directly to a command via **Fleet → Allocate to Command**.
   - Creates a FleetVehicleAssignment (released to command); vehicle moves to AT_COMMAND_POOL at that command.
   - The command (Area Controller) acknowledges receipt on the vehicle detail page (**Mark Received**).

---

## Summary one-liner

**Area Controller (Head of Unit) sees vehicles and officers only for his/her command; CGC sees all vehicles, officers, and command across the whole system.**
