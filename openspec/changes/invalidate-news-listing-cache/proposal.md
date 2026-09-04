# Invalidate News Listing Cache

## Why

Publishing or editing news clears the full-page response cache, but leaves the
application cache used by the home and news listing. Visitors can therefore see
stale news lists until the ten-minute TTL expires.

## What Changes

- Invalidate news-dependent listing cache keys whenever a `News` model changes.
- Make the feature test isolate response and listing cache state.

## Non-Goals

- Change cache duration or replace the current file-cache implementation.
- Introduce Redis or cache tags.
