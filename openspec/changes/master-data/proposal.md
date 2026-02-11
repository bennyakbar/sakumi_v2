# Proposal: master-data

## Summary
Implement master data management required for all financial operations.

## Problem
Transactions and obligations cannot be generated without clean data for students, classes, fee types, categories, and matrix rules.

## Scope
- CRUD for classes, student categories, fee types, fee matrix, students
- Validation and uniqueness rules
- Student import/export
- Fee matrix resolution logic

## Out of Scope
- Transaction posting
- Obligation generation
- Reporting/dashboard/notifications

## Dependencies
- foundation-setup

## Success Criteria
- All master entities are manageable
- Fee matrix resolution is deterministic and test-covered
- Import/export round trip is valid
