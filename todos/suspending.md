# Suspending Employees

## How would I setup this?

- Create a model called suspension
- The suspnsion model should have the fields like, from(DATE) to(DATE)
- When an employee is suspended they should not be able to access the backoffice
- I can do that using a middleware
- Have full CRUD for suspensions
- The page should be protected form being accessed by employees and staff who have not been suspended

### Suspension model fields
- id (uuid)
- author_user_id
- from (uuid) 
- to (uuid)
- reason
- created_at
- updated_at
- deleted_at