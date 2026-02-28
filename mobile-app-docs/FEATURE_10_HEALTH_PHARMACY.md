# Feature 10: Health & Pharmacy

> **Source studied:** `PharmacyRequisition.php` (129 lines), `PharmacyDrug.php`, `PharmacyStock.php`, `PharmacyStockMovement.php`, `PharmacyRequisitionItem.php`, `PharmacyProcurement.php`, `PharmacyProcurementItem.php`, `PharmacyExpiredDrugRecord.php`, `PharmacyWorkflowStep.php`, `NotificationService.php` (6 pharmacy notifications)

---

## 1. Feature Overview

The **Health & Pharmacy** module manages the NCS pharmacy supply chain — from drug stock management to requisitions and dispensing. Officers interact with this module primarily through **pharmacy requisitions** (requesting drugs for their command). Pharmacy Officers and Medical Officers manage the supply chain.

---

## 2. Data Models

### `pharmacy_drugs` — Drug Catalog
```
┌────────────────┬──────────┬──────────────────────────────┐
│ Column         │ Type     │ Notes                        │
├────────────────┼──────────┼──────────────────────────────┤
│ id             │ bigint PK│                              │
│ name           │ string   │ Drug name                    │
│ generic_name   │ string   │ Generic/chemical name        │
│ category       │ string   │ Analgesic, Antibiotic, etc.  │
│ unit           │ string   │ Tablet, Capsule, Bottle, etc.│
│ description    │ text     │                              │
│ is_active      │ boolean  │                              │
└────────────────┴──────────┴──────────────────────────────┘
```

### `pharmacy_stocks` — Inventory per Command
```
┌────────────────┬──────────┬──────────────────────────────┐
│ Column         │ Type     │ Notes                        │
├────────────────┼──────────┼──────────────────────────────┤
│ id             │ bigint PK│                              │
│ drug_id        │ bigint FK│ → pharmacy_drugs.id          │
│ command_id     │ bigint FK│ → commands.id                │
│ quantity       │ integer  │ Current stock level          │
│ batch_number   │ string   │ Batch tracking               │
│ expiry_date    │ date     │ Drug expiration date         │
│ unit_price     │ decimal  │ Cost per unit                │
└────────────────┴──────────┴──────────────────────────────┘
```

### `pharmacy_requisitions` — Drug Requests (5-Step Workflow)
```
Status Flow: DRAFT → SUBMITTED → APPROVED → ISSUED → DISPENSED
                                    ↓
                               REJECTED
```

```
┌──────────────────┬──────────┬──────────────────────────────┐
│ Column           │ Type     │ Notes                        │
├──────────────────┼──────────┼──────────────────────────────┤
│ id               │ bigint PK│                              │
│ reference_number │ string   │ Auto: REQ-2026-00001         │
│ command_id       │ bigint FK│ Requesting command           │
│ status           │ string   │ DRAFT/SUBMITTED/APPROVED/    │
│                  │          │ ISSUED/DISPENSED/REJECTED     │
│ notes            │ text     │                              │
│ current_step_order│ integer │ Current workflow step         │
│ created_by       │ bigint FK│                              │
│ submitted_at     │ timestamp│                              │
│ approved_at      │ timestamp│                              │
│ issued_at        │ timestamp│                              │
│ dispensed_at     │ timestamp│                              │
└──────────────────┴──────────┴──────────────────────────────┘
```

### `pharmacy_requisition_items` — Items in Requisition
```
┌─────────────────────┬──────────┬──────────────────────────┐
│ Column              │ Type     │ Notes                    │
├─────────────────────┼──────────┼──────────────────────────┤
│ id                  │ bigint PK│                          │
│ pharmacy_requisition_id│ bigint FK│                       │
│ drug_id             │ bigint FK│ → pharmacy_drugs.id      │
│ requested_quantity  │ integer  │ How many requested       │
│ approved_quantity   │ integer  │ How many approved        │
│ issued_quantity     │ integer  │ How many actually issued │
│ notes               │ text     │                          │
└─────────────────────┴──────────┴──────────────────────────┘
```

---

## 3. Workflow — 5-Step Requisition

```
┌──────────┐     ┌──────────┐     ┌──────────┐     ┌──────────┐     ┌──────────┐
│  DRAFT   │────▶│SUBMITTED │────▶│ APPROVED │────▶│  ISSUED  │────▶│DISPENSED │
│(Pharmacy │     │(To Appr.)│     │(Medical  │     │(Stock    │     │(Given to │
│ Officer) │     │          │     │ Officer) │     │ deducted)│     │ Command) │
└──────────┘     └──────────┘     └──────────┘     └──────────┘     └──────────┘
                                       │
                                       ▼
                                  ┌──────────┐
                                  │ REJECTED │
                                  │ + reason │
                                  └──────────┘
```

### Workflow Steps (Dynamic via `pharmacy_workflow_steps`)
1. **Pharmacy Officer** creates requisition + selects drugs/quantities
2. **Submits** to Medical Officer for approval
3. **Medical Officer** approves (adjusting quantities if needed) or rejects
4. **Pharmacy Officer** issues drugs from stock
5. **Dispensed** — drugs delivered to requesting command

---

## 4. API Endpoints

```
# Drug catalog
GET    /api/v1/pharmacy/drugs                       → List available drugs
GET    /api/v1/pharmacy/drugs/{id}                  → Drug detail

# Stock (Pharmacy Officer view)
GET    /api/v1/pharmacy/stock                       → Stock levels (by command)
GET    /api/v1/pharmacy/stock/low                   → Low stock alerts
GET    /api/v1/pharmacy/stock/expiring              → Expiring drugs

# Requisitions
GET    /api/v1/pharmacy/requisitions                → List requisitions (by role)
POST   /api/v1/pharmacy/requisitions                → Create requisition
GET    /api/v1/pharmacy/requisitions/{id}           → Requisition detail
POST   /api/v1/pharmacy/requisitions/{id}/submit    → Submit for approval
POST   /api/v1/pharmacy/requisitions/{id}/approve   → Approve (Medical Officer)
POST   /api/v1/pharmacy/requisitions/{id}/reject    → Reject (Medical Officer)
POST   /api/v1/pharmacy/requisitions/{id}/issue     → Issue drugs (Pharmacy Officer)
POST   /api/v1/pharmacy/requisitions/{id}/dispense  → Mark as dispensed

# Procurement
GET    /api/v1/pharmacy/procurements                → Procurement orders
POST   /api/v1/pharmacy/procurements                → Create procurement
```

### `POST /api/v1/pharmacy/requisitions` — Create Requisition

**Request:**
```json
{
  "command_id": 5,
  "notes": "Monthly drug supply for Lagos Command clinic",
  "items": [
    { "drug_id": 1, "requested_quantity": 500 },
    { "drug_id": 3, "requested_quantity": 200 },
    { "drug_id": 7, "requested_quantity": 100 }
  ]
}
```

### `POST /api/v1/pharmacy/requisitions/{id}/approve` — Approve

**Request:**
```json
{
  "items": [
    { "id": 1, "approved_quantity": 500 },
    { "id": 2, "approved_quantity": 150 },
    { "id": 3, "approved_quantity": 100 }
  ],
  "comments": "Approved with reduced quantity for item 2 due to limited stock"
}
```

---

## 5. Notifications (6 Types)

| Event | Method | Recipients |
|-------|--------|-----------|
| Requisition submitted | `notifyPharmacyRequisitionSubmitted()` | Medical Officer |
| Requisition approved | `notifyPharmacyRequisitionApproved()` | Pharmacy Officer |
| Requisition rejected | `notifyPharmacyRequisitionRejected()` | Pharmacy Officer |
| Drugs issued | `notifyPharmacyDrugsIssued()` | Requesting command |
| Low stock alert | `notifyPharmacyLowStock()` | Pharmacy Officer |
| Drug expiry alert | `notifyPharmacyDrugExpiring()` | Pharmacy Officer |

---

## 6. Mobile Screens

### Screen 6.1: Pharmacy Dashboard (Pharmacy Officer)

```
┌─────────────────────────────────────┐
│  💊 Pharmacy                        │
│  ─────────────────────────────────  │
│                                     │
│  ┌────────┐ ┌────────┐ ┌────────┐ │
│  │Stock   │ │Low     │ │Expiring│ │
│  │  245   │ │  12    │ │   5    │ │
│  │ items  │ │🔴 alert│ │⚠️ soon │ │
│  └────────┘ └────────┘ └────────┘ │
│                                     │
│  ── REQUISITIONS ──                 │
│  ┌─────────────────────────────┐   │
│  │ REQ-2026-00015              │   │
│  │ Lagos Command · 3 items     │   │
│  │ ⏳ SUBMITTED                │   │
│  └─────────────────────────────┘   │
│                                     │
│  ┌─────────────────────────────┐   │
│  │ REQ-2026-00014              │   │
│  │ Abuja Command · 5 items     │   │
│  │ ✅ DISPENSED                 │   │
│  └─────────────────────────────┘   │
│                                     │
│      [+ New Requisition]            │
└─────────────────────────────────────┘
```

### Screen 6.2: Create Requisition

```
┌─────────────────────────────────────┐
│  ← New Requisition                  │
│  ─────────────────────────────────  │
│                                     │
│  Command: Lagos Command             │
│                                     │
│  [🔍 Search drugs...]              │
│                                     │
│  Selected Items:                    │
│  ┌─────────────────────────────┐   │
│  │ 💊 Paracetamol 500mg       │   │
│  │ Qty: [500]  In Stock: 1200 │   │
│  │                        [✕]  │   │
│  └─────────────────────────────┘   │
│  ┌─────────────────────────────┐   │
│  │ 💊 Amoxicillin 250mg       │   │
│  │ Qty: [200]  In Stock: 450  │   │
│  │                        [✕]  │   │
│  └─────────────────────────────┘   │
│                                     │
│  Notes:                             │
│  [Monthly supply for clinic    ]   │
│                                     │
│  [Cancel]     [Submit Requisition]  │
└─────────────────────────────────────┘
```

---

## 7. React Native Structure

```
src/features/pharmacy/
├── screens/
│   ├── PharmacyDashboardScreen.tsx
│   ├── StockListScreen.tsx
│   ├── DrugSearchScreen.tsx
│   ├── CreateRequisitionScreen.tsx
│   ├── RequisitionDetailScreen.tsx
│   ├── ApproveRequisitionScreen.tsx
│   └── LowStockAlertScreen.tsx
├── components/
│   ├── StockCard.tsx
│   ├── DrugSearchItem.tsx
│   ├── RequisitionCard.tsx
│   ├── RequisitionItemRow.tsx
│   ├── QuantityAdjuster.tsx
│   └── StockLevelBadge.tsx
├── api/
│   └── pharmacyApi.ts
└── types/
    └── pharmacy.ts
```

### TypeScript Types
```typescript
export interface PharmacyDrug {
  id: number;
  name: string;
  generic_name: string;
  category: string;
  unit: string;
  description: string;
}

export interface PharmacyStock {
  id: number;
  drug: PharmacyDrug;
  command_id: number;
  quantity: number;
  batch_number: string;
  expiry_date: string;
  unit_price: number;
}

export type RequisitionStatus = 'DRAFT' | 'SUBMITTED' | 'APPROVED' | 'ISSUED' | 'DISPENSED' | 'REJECTED';

export interface PharmacyRequisition {
  id: number;
  reference_number: string;
  command_id: number;
  command?: Command;
  status: RequisitionStatus;
  notes: string | null;
  items: PharmacyRequisitionItem[];
  created_by: number;
  submitted_at: string | null;
  approved_at: string | null;
  issued_at: string | null;
  dispensed_at: string | null;
}

export interface PharmacyRequisitionItem {
  id: number;
  drug: PharmacyDrug;
  requested_quantity: number;
  approved_quantity: number | null;
  issued_quantity: number | null;
  notes: string | null;
}
```

---

## 8. Testing Checklist

- [ ] Browse drug catalog
- [ ] View stock levels per command
- [ ] Create requisition with multiple drugs
- [ ] Submit requisition → Medical Officer notified
- [ ] Medical Officer approves with quantity adjustments
- [ ] Medical Officer rejects with reason
- [ ] Issue drugs → stock levels decrease
- [ ] Mark as dispensed
- [ ] Low stock alert notification
- [ ] Expiring drug alert notification
- [ ] Requisition step timeline display
- [ ] Auto-generated reference numbers
