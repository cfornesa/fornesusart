# External Dependencies

## Google Fonts CDN

| Item | Value |
|------|-------|
| Purpose | Serving Cinzel Decorative, Lora, Courier Prime typefaces |
| Off-domain data | Yes — font requests include visitor IP address |
| What breaks if unavailable | Fonts fall back to system serif/monospace; layout and aesthetic degrade but site remains functional |
| Self-hosting alternative | Download font files and place in `public/assets/fonts/`; update `@font-face` rules in `style.css` |
| Cost | Free |

To self-host: download from fonts.google.com, place `.woff2` files in `public/assets/fonts/`, add `@font-face` declarations at the top of `public/assets/css/style.css`.
