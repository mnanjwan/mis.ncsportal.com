# Vehicle Model System Documentation

## Overview

The vehicle creation system has been updated to use a **Vehicle Model** approach that ensures uniformity and prevents repetition. This system separates vehicle specifications (Make, Type, Year) from individual vehicle instances (Chassis Number, Engine Number).

## How It Works

### 1. Vehicle Models (Template)

A **Vehicle Model** is a combination of:
- **Make** (e.g., Toyota, Honda)
- **Vehicle Type** (e.g., Pickup, Saloon, SUV, Bus)
- **Year of Manufacture** (e.g., 2018, 2019)

**Example:** "Toyota PickUp 2018" is a unique vehicle model.

### 2. Vehicle Instances

When adding a vehicle to inventory, you:
1. **Select** an existing vehicle model from a dropdown (e.g., "Toyota PickUp 2018")
   - OR **Create** a new model if it doesn't exist
2. **Add** vehicle-specific identifiers:
   - **Chassis Number** (Primary Key - must be unique)
   - **Engine Number** (Primary Key - must be unique, but can be changed later)
   - Registration Number (optional)
   - Other details (received date, notes, etc.)

## Benefits

1. **Uniformity**: All "Toyota PickUp 2018" vehicles are grouped together
2. **No Repetition**: The same model specification is stored once and reused
3. **Easy Search**: Searching for "Toyota PickUp 2018" finds all vehicles with that model
4. **Data Integrity**: Chassis Number and Engine Number remain unique identifiers

## Database Structure

### `fleet_vehicle_models` Table
- `id` (Primary Key)
- `make` (e.g., "Toyota")
- `vehicle_type` (e.g., "PICKUP", "SALOON", "SUV", "BUS")
- `year_of_manufacture` (e.g., 2018)
- Unique constraint on `(make, vehicle_type, year_of_manufacture)`

### `fleet_vehicles` Table
- `id` (Primary Key)
- `vehicle_model_id` (Foreign Key → `fleet_vehicle_models.id`)
- `chassis_number` (Unique Primary Identifier)
- `engine_number` (Unique Primary Identifier, nullable)
- `reg_no` (Unique, nullable)
- Other fields (service_status, lifecycle_status, etc.)

## User Interface

### Vehicle Intake Form

1. **Vehicle Model Selection**:
   - Dropdown showing all existing models (e.g., "Toyota PickUp 2018")
   - Option to "Create New Model" if needed

2. **Create New Model** (when selected):
   - Make field (required)
   - Vehicle Type dropdown (Saloon, SUV, Bus, Pickup)
   - Year of Manufacture (required)
   - Preview shows: "Make VehicleType Year"

3. **Vehicle Details**:
   - Chassis Number (required, unique)
   - Engine Number (optional, unique)
   - Registration Number (optional)
   - Received Date, Notes, etc.

## Usage Example

### Scenario: Adding a Toyota PickUp 2018

1. **If model exists**:
   - Select "Toyota PickUp 2018" from dropdown
   - Enter Chassis Number: "ABC123456789"
   - Enter Engine Number: "ENG001234"
   - Submit

2. **If model doesn't exist**:
   - Select "+ Create New Model"
   - Enter Make: "Toyota"
   - Select Vehicle Type: "Pickup"
   - Enter Year: "2018"
   - Preview shows: "Toyota Pickup 2018"
   - Enter Chassis Number: "ABC123456789"
   - Enter Engine Number: "ENG001234"
   - Submit

### Result

- Vehicle Model "Toyota PickUp 2018" is created (if new)
- Vehicle instance is created with:
  - `vehicle_model_id` → points to "Toyota PickUp 2018"
  - `chassis_number` → "ABC123456789" (unique)
  - `engine_number` → "ENG001234" (unique)

## Searching Vehicles

When searching for vehicles by model (e.g., "Toyota PickUp 2018"):
- All vehicles with `vehicle_model_id` matching that model are returned
- Results are uniform and grouped correctly
- No duplicate model entries

## Backward Compatibility

- Existing vehicles without a `vehicle_model_id` still work
- The system falls back to using `make`, `vehicle_type`, and `year_of_manufacture` fields
- Display name uses `vehicleModel->display_name` if available, otherwise falls back to concatenated fields

## Key Features

✅ **Chassis Number** = Primary Key (unique, required)
✅ **Engine Number** = Primary Key (unique, optional, can be changed)
✅ **Vehicle Model** = Template (Make + Type + Year)
✅ **Uniform Grouping** = All vehicles of same model grouped together
✅ **Easy Search** = Search by model name finds all matching vehicles
✅ **No Repetition** = Model specifications stored once and reused

## Technical Implementation

- **Model**: `FleetVehicleModel` with `display_name` accessor
- **Relationship**: `FleetVehicle` belongs to `FleetVehicleModel`
- **Uniqueness**: Database constraints ensure chassis_number and engine_number are unique
- **Validation**: Form validates model selection or new model creation
- **Queries**: Updated to use `vehicleModel` relationship where applicable
