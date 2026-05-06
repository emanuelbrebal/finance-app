# Plan: Wishlist — Auto-fill from Product URL

## Goal

User pastes a product URL → system extracts name, price, and store automatically. User reviews, adjusts if needed, then saves. Eliminates the friction of manually filling a product form.

---

## Current flow (to replace)

User fills: name, target_price, reference_url, photo, priority, category → tedious, especially on mobile.

## New flow

1. User pastes URL (e.g. `https://www.amazon.com.br/dp/B09X...`)
2. System fetches the page and extracts: name, price, store name, image URL
3. Form pre-fills with extracted data (editable)
4. User tweaks if needed and saves

---

## Extraction strategy

### Server-side HTTP fetch (Guzzle)
The backend fetches the URL, parses HTML, extracts structured data.

**Priority order for extraction:**
1. `application/ld+json` with `@type: Product` (schema.org) — most reliable
2. Open Graph meta tags (`og:title`, `og:image`, `og:price:amount`)
3. Store-specific CSS selectors (fallback)
4. Regex for price pattern `R$\s*[\d.,]+` near the title (last resort)

### Supported stores (Phase 1 — explicit selectors)

| Store | Price selector | Name source |
|---|---|---|
| Amazon BR | `.a-price .a-offscreen`, `#priceblock_ourprice` | `#productTitle` |
| Mercado Livre | `[class*="price-tag-fraction"]` + separator + `[class*="price-tag-cents"]` | `h1.ui-pdp-title` |
| Magazine Luiza | `[data-testid="price-value"]` | `h1[class*="Title"]` |
| Shopee | `._3_ISdg` or `meta[property="og:description"]` | `og:title` |
| Americanas | `[data-testid="price"]` | `h1[class*="product-title"]` |
| Casas Bahia | same pattern as Americanas (same group) | same |
| **Fallback** | schema.org / Open Graph / regex | `og:title` |

### What we DON'T do
- No headless browser (Puppeteer) — adds complexity, Docker weight, and maintenance
- No paid APIs
- No caching of product pages server-side (fetch on demand, not stored)

---

## Backend

### New dependency
- `symfony/dom-crawler` — already in many Laravel stacks, lightweight HTML parser
- `guzzlehttp/guzzle` — already a Laravel dependency

### Service
```
App\Services\ProductExtractorService
  extract(string $url): ProductDTO
```

`ProductDTO`:
```php
class ProductDTO {
    public string $name;
    public ?string $price;         // "1299.99" or null
    public ?string $store_name;    // "Amazon" or null
    public ?string $image_url;     // absolute URL or null
    public bool $extraction_succeeded;
    public string $source;         // 'schema_org' | 'open_graph' | 'store_selector' | 'regex' | 'failed'
}
```

### New endpoint
```
POST /api/wishlist/extract-from-url
Body: { url: string }
Response: { data: { name, price, store_name, image_url, source, url } }
```

- [ ] No auth bypass — user must be logged in
- [ ] Rate limit: 10 requests per minute per user (prevents abuse)
- [ ] Timeout: 8 seconds max per fetch (Guzzle timeout)
- [ ] If fetch fails or times out: return 422 with `{ message: "Não conseguimos extrair dados desse link. Preencha manualmente." }`
- [ ] If price not found: return name + store (partial success) with `price: null`
- [ ] Validate URL is http/https only (no local/private IPs — SSRF protection)
- [ ] `StoreExtractFromUrlRequest` — validates URL format + blocks private IP ranges

### SSRF protection (security requirement)
Before fetching, resolve the URL's hostname and reject if it resolves to:
- `127.x.x.x`, `10.x.x.x`, `172.16-31.x.x`, `192.168.x.x`
- `localhost`, `*.local`, `*.internal`
- IPv6 loopback `::1`

```php
$ip = gethostbyname(parse_url($url, PHP_URL_HOST));
if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
    abort(422, 'URL inválida.');
}
```

### Updated `StoreWishlistItemRequest`
Add `reference_url` as a required field OR add a `from_url` flow:
- If `reference_url` is set: system auto-filled flow
- If not: manual flow (name + price required)

---

## Frontend

### Updated `WishlistPage` / create form
- Replace the "New item" button → opens a dialog
- First screen of dialog: URL input field with "Buscar produto" button
  - If URL provided: fetch → show pre-filled form with "Buscando dados..." skeleton → fill on response
  - If "Preencher manualmente" link clicked: show blank form
- After extraction: show form pre-filled, user can edit every field
- `image_url` extracted: show thumbnail preview if available

### New components
- [ ] `WishlistAddDialog` — two-step: URL input → review/edit form
- [ ] `ProductURLInput` — input field + "Buscar" button with loading state
- [ ] `ProductPreview` — thumbnail + name + price in a compact preview card
- [ ] `ExtractionBadge` — small badge showing source ("via Amazon", "via Open Graph") so user knows how reliable the data is

### New hook
- [ ] `src/hooks/mutations/useExtractFromUrl.ts` — `POST /api/wishlist/extract-from-url`

### UX rules
- If extraction fails: show the manual form with a banner "Não conseguimos buscar o produto, preencha você mesmo"
- If price not found: show the form with price field empty + helper "Preço não encontrado — insira manualmente"
- "Extraction source" badge is subtle (small gray text), not prominent
- Image thumbnail is displayed as background in the card if available, not a mandatory field
