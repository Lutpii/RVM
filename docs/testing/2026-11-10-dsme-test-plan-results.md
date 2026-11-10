# RVM Web Application: Test Plan Results (T01–T09) and Issue Register

Reference: *RVM Web Application Technical Assessment & Development Improvement Report* (7 Nov 2026), §7 Suggested Test Plan and §8 Issue Register.

| | |
|---|---|
| Date run | 10 Nov 2026, 11:00–11:20 (MYT) |
| Environment | Local test stack (report §10 "safe test environment"): Laravel `php artisan serve` on 127.0.0.1:8000, local MySQL `rvm_db`, Vue/Vite dev build. No production (TiDB / Vercel) data was read or changed. |
| Code version | branch `feat/dsme-assessment-2`, commit `c56c7e4` (round-1 changes + U02/U04/U05/U06/§6 logging) |
| Test data | `php artisan rvm:demo-data --days=30` (demo users, 30 days of transactions, demo machine RVM-DSME) plus one throwaway recycler `t01.recycler@test.local` with 3 items (+6, +8, −2 = 12 points). Both were removed after the run (`rvm:demo-data --purge`). |
| Tooling | Playwright driving Microsoft Edge; API checks with the browser session token; DB comparison with `php artisan tinker`. |
| Automated suites at this commit | Backend (PHPUnit) 239 passed · Frontend (Vitest) 202 passed |

Evidence screenshots are in `docs/testing/2026-11-10/`.

## Results

| Test | Action | Expected (report §7) | Actual | Result |
|---|---|---|---|---|
| T01 | Open Recycler Home / Activity / Scan / Rewards / Account | All routes load without error; correct role navigation | All five routes loaded (titles "Dashboard", "Activity", "Scan QR", "Rewards", "Account"); 0 console errors, 0 failed API calls. Evidence `T01-*.png` | **Pass** |
| T02 | Open profile and inspect display | Identity and role consistent across pages | Account page shows "T01 Recycler" and role "Recycler"; Dashboard total 12; `/api/auth/me` returns role `user`, name "T01 Recycler", 12 points | **Pass** |
| T03 | Log in with administrator account | Admin landing page and authorised modules shown | `#/admin` opens with Dashboard, Machines, Rewards, Users, Transactions, Sessions, Audit Log, Detection Review. Evidence `T03-admin-dashboard.png` | **Pass** |
| T04 | Attempt direct admin URL as Recycler | Access denied, without protected data exposure | `#/admin` redirects the recycler to `#/dashboard`, no admin content rendered. Direct API calls with the recycler's token: `/api/admin/users`, `/stats`, `/transactions`, `/logs` all **403** "Access denied. Admin only.", no data in the body. Evidence `T04-recycler-admin-url.png` | **Pass** |
| T05 | Inspect transaction list and filters | Correct records, totals, pagination, no console/API errors | 753 transactions, 20 per page, 38 pages ("1 – 20 of 753"); page 2 returns the next 20; status valid 655 / rejected 98 (= 753); last 7 days 82; search "plastic" 269; an invalid date is refused with 422. Evidence `T05-admin-transactions.png` | **Pass** |
| T06 | Inspect rewards ledger and totals | Balances reconcile to visible transactions | Recycler balance 12 = sum of points-history rows (6 + 8 − 2). See BUG-001 for the row order when two rows share a timestamp | **Pass** |
| T07 | Inspect machine dashboard and detail | Status and last-seen timestamp clearly identified | Each machine card shows status and heartbeat line; machines without a heartbeat show "Never reported"; after one heartbeat RVM-DSME shows "Online · last seen 45s ago". Evidence `T07-admin-machines.png`, `T07-machine-online.png` | **Pass** |
| T08 | Inspect reports and date filters | Totals agree with source transactions | Formal report for 4–10 Nov: TOTAL row plastic 29, aluminium 30, paper 11, glass 8, reject rate 4.9 %, points 1219, unique users 9, **identical** to direct DB counts for the same range. Export endpoint returns 200 `.xlsx` | **Pass** |
| T09 | Check desktop and mobile layouts | No clipping; controls readable and keyboard accessible | At 390×844 and 1366×800, Dashboard / Activity / Rewards / Account / Admin have no horizontal page scroll; Tab moves focus to a visible, outlined control. Evidence `T09-mobile-*.png` | **Pass** |

**Summary: 9 of 9 tests passed. No Critical or High issue found. Three Low issues recorded below.**

## Issue Register (§8)

| Issue ID | Page / role | Steps / evidence | Severity | Status |
|---|---|---|---|---|
| BUG-001 | Activity / Recycler (`GET /api/user/points-history`) | Two points-history rows created in the same second come back in arbitrary order (query sorts by `created_at` only). In T06 the top row showed `balance_after` 6 instead of 12. Balance itself is correct. Expected: newest row first, ties broken by ID | Low | Open |
| BUG-002 | Admin panel / Admin | Change `?tab=machines` to `?tab=transactions` in the address bar without reloading: the URL changes but the panel stays on the old tab. Clicking the sidebar or reloading works. Expected: the tab follows the URL | Low | Open |
| BUG-003 | Activity / Recycler | A recycling session that earned 0 points shows "0" in red, the colour used for deductions. Expected: neutral colour for 0. Evidence `T09-mobile-activity.png` | Low | Open |

Not an issue: on a phone, the material filter row scrolls sideways inside its own row ("Paper" sits at the edge); the page itself does not scroll horizontally.

## Not covered by this run

- **Final acceptance (§9):** a real end-to-end deposit from the kiosk to the cloud, a simulated network interruption on the kiosk, a fault alert raised by the real machine, and a reconciled report of that deposit. These need the DSME kiosk connected to the cloud backend with its machine key.
- Performance with realistic volumes, and data retention / backup procedures (§6).
