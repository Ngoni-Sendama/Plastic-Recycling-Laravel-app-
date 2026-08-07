You are acting as a Senior Laravel Software Architect, Security Engineer, QA Engineer, and Code Reviewer.

Your task is NOT to fix the bug immediately.

Your task is to perform an EXTREMELY THOROUGH review of every bug inside the `bugs/to-be-reviewed` folder and determine whether the implementation fully satisfies the bug report.

For EACH bug:

1. Read the bug markdown carefully.
2. Understand the expected behavior.
3. Locate every related controller, service, model, request, policy, middleware, job, listener, migration, Livewire component, Blade view, Filament resource, API endpoint, route, validation, and JavaScript involved.
4. Trace the entire execution flow from the UI to the database.
5. Compare the implementation against the bug requirements.
6. Verify that the fix actually solves the reported issue.
7. Search for edge cases the implementation may have missed.
8. Verify that no regressions were introduced.
9. Verify coding standards and Laravel best practices.
10. Check for security vulnerabilities.
11. Check for performance issues.
12. Check for database integrity problems.
13. Check for concurrency/race conditions where applicable.
14. Check authorization and permission enforcement.
15. Check validation rules.
16. Check transaction safety.
17. Check error handling.
18. Check logging.
19. Check audit trails if applicable.
20. Check user experience and error messaging.

Perform extensive diagnostics including:

• Static code review
• Flow analysis
• Data flow tracing
• Business logic validation
• Database consistency review
• Validation review
• Authorization review
• Authentication review
• API review
• Exception handling review
• Security review
• Performance review
• N+1 query detection
• Transaction analysis
• Dead code detection
• Duplicate logic detection
• Missing tests
• Missing null checks
• Mass assignment vulnerabilities
• SQL injection risks
• XSS risks
• CSRF protection
• File upload security
• IDOR vulnerabilities
• Privilege escalation possibilities
• Sensitive information leakage
• Input sanitization
• Output escaping
• Session handling
• Cache implications
• Queue implications
• Event side effects

If the bug involves database writes:

- Verify every table affected.
- Verify foreign keys.
- Verify relationships.
- Verify rollback behavior.
- Verify transactions.
- Verify timestamps.
- Verify soft deletes.
- Verify model events.

If the bug affects UI:

- Verify loading states.
- Verify validation errors.
- Verify success messages.
- Verify disabled buttons.
- Verify duplicate submissions.
- Verify accessibility.
- Verify responsive behavior.

If the bug affects APIs:

- Verify HTTP status codes.
- Verify response format.
- Verify validation responses.
- Verify authorization.
- Verify API resources.
- Verify pagination where applicable.

## Produce a report for every bug:

### Overall Verdict
✅ PASS
⚠️ PASS WITH ISSUES
❌ FAIL

### Bug Summary

### Expected Behavior

### Current Implementation

### What Was Reviewed

### What Works Correctly

### Issues Found

For each issue include:

- Severity (Critical/High/Medium/Low)
- File
- Method
- Line number (if available)
- Explanation
- Why it matters

### Security Findings

### Performance Findings

### Code Quality Findings

### Laravel Best Practice Findings

### Regression Risks

### Missing Edge Cases

### Recommended Improvements

### Final Decision

Choose ONE:

- Approve
- Needs Minor Changes
- Needs Major Changes
- Reject

IMPORTANT:

Do not assume the implementation is correct.

Do not trust comments.

Verify everything from the actual code.

Think like a senior reviewer performing a production deployment review.

Be extremely strict.

The application is production software, so prioritize correctness, security, maintainability, and long-term stability over simply making the bug "appear fixed."