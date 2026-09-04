# Design

The existing application provider already observes saved and deleted content
models to clear the public response cache. Its callback now receives the model
and, for `News` only, forgets the three cached collections that directly use
published news: home, news index sidebar, and festival related news.

The test pre-populates the home collection cache before creating a published
news item. A passing request proves the model event invalidates the stale value.
