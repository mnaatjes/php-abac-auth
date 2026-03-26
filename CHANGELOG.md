# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [0.1.0] - 2026-03-26

### Added
- Initial project structure for ABAC (Attribute-Based Access Control) library.
- Core components: PEP (Policy Enforcement Point), PDP (Policy Decision Point), PIP (Policy Information Point), and PRP (Policy Retrieval Point).
- Basic implementations for Policy Management (JSON, YAML placeholders).
- Support for Attribute-based evaluation logic.
- Comprehensive documentation in `docs/` and `PLAN.md`.
- Unit testing suite (Work in progress).

### Changed
- Updated `.gitignore` for better environment management.
- Refactored `README.md` to provide clearer project overview and quick start guide.

### Fixed
- Permission issues with Docker-generated cache files (resolved via ownership reclamation).
