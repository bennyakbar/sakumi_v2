# Design: master-data

## Architecture Touchpoints
- Master controllers, requests, services, models
- DB constraints/indexes for identity and lookup
- Import/export adapters

## Key Decisions
- Use effective-date bounded fee matrix
- Specificity priority: class+category over partial/global
- Keep student lifecycle via status/soft-delete policy

## Risks
- Ambiguous fee matrix resolution if ordering unclear
- Dirty imports causing duplicates

## Validation
- Resolution unit tests
- CRUD feature tests
- Import/export tests with edge cases
