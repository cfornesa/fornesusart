<!-- Agent reads this file at every session start. Surface any entry marked PENDING CONFIRMATION
to the human before proceeding. Do not act on a pending entry — wait for explicit confirmation
or rejection. -->

2026-06-04 · Redesigned the admin Media Library into a dual-pane "Darkroom Gallery" split-view workspace with 1:1 image tiles, a sticky details panel with copyable HTML embed and direct URL inputs, and an interactive drag-and-drop/browse upload zone. Added dynamic CSS cache busters to layout.php.
2026-06-04 · Standardized copyable inputs with a border-collapsed side-by-side design, unified destructive actions with a new `.admin-btn-danger` outline button style, and enforced Safari mobile compatibility with zero rounded corners.
2026-06-04 · Reworked the admin Media Library into an admin-native grid manager with asset cards, shared admin button/input styling, a reusable notice pattern, and a wider content mode instead of the earlier bespoke split-gallery treatment.
2026-06-04 · Fixed admin view layout include paths so `/admin` pages consistently render through the shared admin layout and navigation rather than risking public-site chrome.
