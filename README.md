# Proposal contract flow

## Runtime contract storage

- `pay.html` generates the signed PDF in the browser and downloads it locally for the client.
- After generation, `pay.html` sends JSON payload (`pdfBase64` + contract fields) to `save_contract.php`.
- `save_contract.php` saves two runtime files in `signed-contracts/`:
  - `<base>.pdf`
  - `<base>.json` metadata (client/plan/payment/date)
- Git keeps only `signed-contracts/.gitkeep`; all runtime generated files stay untracked.

## Contractor signature asset

- `pay.html` references external `signature-amir-kiani.svg` beside `pay.html`.
- Do not commit signature files to this repository.
- If the SVG is missing, PDF generation still works and falls back to `Kiani Development` text.
