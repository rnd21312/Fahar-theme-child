# Portfolio Likes Architecture

## Ownership

`fahar-elementor-core` owns Like storage, validation, mutation rules, REST
routes, and its public PHP API. The Child Theme owns only presentation and a
defensive adapter to that API. Like data must survive a theme switch, and no
template may read Like post meta or user meta directly.

Any future Single Portfolio Like UI belongs in an Elementor-owned template or
companion-plugin widget. Task 20 adds no PHP helpers, UI, scripts, routes, or
storage.

## Recommended v1

- Only authenticated WordPress users with the `read` capability can Like.
- User meta is the canonical membership record; post meta is a denormalized
  aggregate count for fast reads.
- Mutations use a companion-plugin REST route and WordPress cookie
  authentication with a REST nonce.
- Anonymous Likes are unavailable in v1.

## Identity Model

The actor is the authenticated WordPress user ID. A `(user ID, portfolio post
ID)` pair has at most one active Like. `POST` changes an unliked pair to liked;
`DELETE` changes a liked pair to unliked.

Repeated `POST` and `DELETE` transitions do not change the count. The future
endpoint returns a structured `409` error (`already_liked` or `not_liked`) with
the authoritative current state and count.

Anonymous visitors can view portfolios and counts but cannot Like. V1 creates
no anonymous token or cookie and uses neither IP addresses nor fingerprints as
identity. A first-party anonymous-token model requires a separate privacy and
abuse review before implementation.

## Storage Model

The companion plugin will own these conceptual keys:

- User meta `_fahar_core_liked_portfolio_ids`: canonical, unique array of
  positive portfolio post IDs for one user.
- Post meta `_fahar_core_portfolio_like_count`: non-negative integer cache of
  active user Likes for one portfolio.

The user-meta array keeps v1 WordPress-native and reversible. Membership checks
must use strict integer comparison and normalize duplicates before saving. The
count is never accepted from a client and is never the canonical membership
record.

This model is intended for modest per-user Like volumes. It avoids scans during
normal page renders: membership uses the current user's cached meta and the
count is one local post-meta read. A high-volume relationship table is deferred
to migration rather than introduced speculatively.

## Data Integrity

Future mutations must acquire two short-lived locks in a fixed order: actor,
then portfolio. Locks use atomic, non-autoloaded `add_option()` records with
Fahar-prefixed names, an expiry timestamp, stale-lock recovery, and `finally`
cleanup. This serializes concurrent requests without a custom table.

While locked, the plugin must re-read canonical user state before changing it.
It updates the count only after a real state transition, clamps the count at
zero, clears relevant object-cache entries, and rolls back or returns
`WP_Error` if either write fails. Double Like and double Unlike never adjust the
aggregate.

The post-meta count is explicitly denormalized and can become stale after an
interrupted multi-write operation. A future WP-CLI/admin reconciliation command
may rebuild it from canonical membership outside frontend requests. Normal
renders must never scan all users or portfolios.

## Plugin PHP API

The companion plugin will expose a small stable API:

```php
fahar_core_get_portfolio_like_count( int $post_id ): int
fahar_core_is_portfolio_liked( int $post_id, int $user_id = 0 ): bool
fahar_core_like_portfolio( int $post_id, $actor = null ): array|WP_Error
fahar_core_unlike_portfolio( int $post_id, $actor = null ): array|WP_Error
```

`$user_id = 0` and `$actor = null` mean the current authenticated user. Mutation
success returns `array( 'liked' => bool, 'count' => int )`. Invalid portfolio,
authentication, transition conflict, lock contention, and storage failure
return stable `WP_Error` codes with appropriate HTTP status data. Read methods
return `0` or `false` for invalid/unavailable state and never expose storage
keys or user IDs.

## Theme Adapter Contract

A future presentation task may add only these defensive Theme helpers:

```php
fahar_theme_portfolio_likes_available(): bool
fahar_theme_get_portfolio_like_count( $post = null ): int
fahar_theme_is_portfolio_liked( $post = null ): bool
```

They must validate through the existing Fahar portfolio adapter, guard plugin
calls with `function_exists()`, and return `false`, `0`, and `false`
respectively when the companion plugin is inactive. Templates consume only
these helpers. They perform no writes and know no storage keys.

Initial count and pressed state will be server-rendered through this adapter.
The future control is omitted when availability is false; it must not display a
fake state while the backend is absent.

## Endpoint Contract

The future companion plugin will register REST routes:

```text
POST   /fahar/v1/portfolio/<id>/like
DELETE /fahar/v1/portfolio/<id>/like
```

REST is preferred over `admin-ajax.php` because it provides method semantics,
permission callbacks, structured errors, and a reusable contract for the Theme
and future Elementor integrations. Successful responses are minimal:

```json
{"liked": true, "count": 42}
```

The server validates a positive ID, the configured portfolio post type,
published/accessibly viewable status, authenticated actor, and requested state
transition. It sanitizes route input, ignores client-provided counts, and is
authoritative for returned state.

Without JavaScript, future Like UI may be omitted or non-interactive. Portfolio
content and navigation remain fully usable; no false success is shown. Task 20
does not register either route.

## Security

REST permission callbacks require an authenticated current user with `read`
capability. Cookie-authenticated browser requests include `X-WP-Nonce` created
for `wp_rest`; the nonce mitigates CSRF but does not replace authentication or
authorization. The plugin validates the portfolio on every mutation and returns
structured errors without leaking storage details.

Anonymous requests receive an authentication error. A nonce is never treated as
anonymous identity. V1 adds no public mutation surface, CAPTCHA, external
anti-bot service, or IP throttling; WordPress account authentication provides
the initial abuse boundary.

## Privacy

V1 stores only the WordPress user-to-portfolio relationship needed for the
feature. It forbids Like identity based on IP address, device/browser
fingerprinting, cross-site identifiers, third-party tracking SDKs, or analytics.
No anonymous cookie is created.

Any later anonymous first-party token must have a single Like-deduplication
purpose, documented expiry, `SameSite=Lax`, `Secure` on HTTPS, no personal-data
requirement, no cross-site use, and an explicit statement that clearing browser
storage defeats continuity. It is not part of v1.

## Accessibility

The future Elementor/plugin control must be a real
`<button>` with visible text or an accessible name, visible focus, keyboard
operation, and `aria-pressed`. Active state cannot rely on color alone. State
and count changes must be announced accessibly without moving focus. The
control remains compact, uses the Theme's Desert Gold active treatment, and
copies no third-party social assets.

## Plugin-Unavailable Behavior

If `fahar-elementor-core` is inactive, normal WordPress/Elementor Single
rendering continues without errors, the Like control is unavailable, and the
Theme performs no substitute writes. Counts and mutations are never
reimplemented in the Child Theme.

## Performance

Future plugin reads are small local WordPress meta lookups with normal object
caching. Count lookup is O(1)-style; membership is linear only within the
current user's Like array, which is the documented v1 scale limit. Mutations
touch one user's canonical state, one portfolio count, and short-lived lock
records. Frontend requests perform no full user/portfolio scan, remote request,
or expensive aggregation.

The same companion-plugin PHP and REST APIs are the source for future Elementor
Dynamic Tags, widgets, and portfolio components; Theme templates never become
the business-logic implementation.

## Future Migration

At higher scale, the plugin may migrate canonical membership to a custom table
with a unique `(user_id, post_id)` key, reconcile aggregate counts, or merge a
separately approved anonymous token into an account. The public plugin API,
REST payload, and Theme adapter contract remain stable, so the Theme requires no
storage-aware changes.

## Deferred Features

- Like backend, meta writes, mutation locks, and reconciliation tooling.
- REST route registration, nonce delivery, and frontend request handling.
- Like/count UI and accessible live-state behavior.
- Anonymous tokens, abuse controls, and anonymous-to-account merging.
- Elementor Dynamic Tags, widgets, activity feeds, notifications, and Like
  lists.
- Custom relationship tables and data migrations.
