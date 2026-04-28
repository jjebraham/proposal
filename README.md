# Proposal contract flow

## Runtime contract storage

- Signed contract PDFs are saved by `save_contract.php` at runtime.
- Saved files are written to `signed-contracts/` on the server.
- The repository keeps only `signed-contracts/.gitkeep` to preserve the directory.
- Generated files (`*.pdf`, images, and uploaded assets) are ignored by git.

## Contractor signature asset

- `pay.html` references `signature-amir-kiani.svg` as an external file beside `pay.html`.
- Do not commit signature files to this repository.
- If the signature file is missing, PDF generation falls back to the text `Kiani Development`.
