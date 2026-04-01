## Thinking discipline for every prompt

- Apply this guidance on every user prompt, not only when a specific file pattern matches.
- Before starting work, determine the scope of the request, the main subject, the relevant dependencies, and the files or systems likely involved.
- Strive to understand the full context before acting. If something is unclear, infer from the repository context and best engineering practices, then validate against the files in scope.
- When planning, first think through the whole task in your head. Perform a dry run of the intended changes step by step and visualize the code flow, data flow, dependencies, edge cases, and likely pitfalls.
- Re-check your own assumptions, explanations, and prior conclusions. If something feels off, question it, backtrack if needed, and adjust the approach before continuing.
- Prefer the simplest solution that works. Apply Occam's Razor whenever there is a choice between equivalent approaches.
- After each important decision, sanity-check the consequences for the project goals, architecture, contracts, maintainability, and future changes.

## Test-first directive (TDD)

- When implementing bugfixes or features, write tests that first surface the current bug or missing behavior.
- Prefer tests that verify observable outcomes and user-visible behavior over internal implementation details.
- Confirm the test fails for the correct functional reason before changing production code.
- Implement the minimal code change soon after to move the failing test to passing (red -> green), then refactor safely.

## Change-planning thoroughness

- When planning, reviewing, or implementing changes, assess not only what should be added or refactored, but also what becomes obsolete, misleading, fragile, or unnecessary under the new design.
- Re-check touched areas for architecture, data flow, ownership, and contract changes. Do not preserve inference, synchronization, coupling, fallback, or compatibility logic by default.
- Treat the seam between old and new code as a high-risk zone. Look specifically for dead paths, misplaced responsibilities, deceptive behavior, and brittle transitions.
- Prefer coherent replacement and cleanup: if the new design removes the need for a legacy path, explicitly call for its removal.
