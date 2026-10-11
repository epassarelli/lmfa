## ADDED Requirements

### Requirement: Publish guide page SHALL be public and indexable
The system SHALL expose `GET /guia-para-publicar` without authentication, rendered with the public layout, a unique title, meta description and a canonical URL on the `.com` domain.

#### Scenario: Guide is publicly accessible
- **WHEN** a visitor requests `GET /guia-para-publicar`
- **THEN** the system returns HTTP `200` with a single `h1` and the canonical URL `https://mifolkloreargentino.com/guia-para-publicar`

### Requirement: Guide SHALL describe requirements per content type
The page SHALL include a section for events, festivals, artists, news and discography/songs, each listing required and recommended data, plus sections on images, editorial criteria and how to send material. It SHALL NOT invite the submission of full song lyrics.

#### Scenario: Sections are present
- **WHEN** the guide is rendered
- **THEN** it contains headings for each content type, images, editorial criteria and submission channels

### Requirement: Guide SHALL be discoverable
The site footer SHALL link to the guide and the static sitemap SHALL include its URL.

#### Scenario: Footer and sitemap
- **WHEN** any public page is rendered and `/sitemap-estaticas.xml` is requested
- **THEN** the footer contains a link to `/guia-para-publicar` and the sitemap lists that URL
