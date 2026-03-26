# ABAC Auth (Attribute-Based Access Control)

A simple, reusable PHP library for Attribute-Based Access Control (ABAC). This library provides a flexible framework for making access decisions based on the attributes of subjects, resources, actions, and environments.

## Features

- **Decoupled Architecture**: Separate components for Policy Enforcement (PEP), Decision (PDP), Information (PIP), and Retrieval (PRP).
- **Flexible Policies**: Define complex rules using a simple logical structure.
- **Extensible Expressions**: Support for various logical and comparison operations.
- **Easy Integration**: Adapters for different policy storage formats and attribute sources.
- **PSR-Compliant**: Follows modern PHP standards for interoperability.

## Architecture

The library follows the standard ABAC architecture:

- **PEP (Policy Enforcement Point)**: Intercepts access requests and enforces decisions.
- **PDP (Policy Decision Point)**: Evaluates requests against applicable policies.
- **PIP (Policy Information Point)**: Provides missing attributes required for evaluation.
- **PRP (Policy Retrieval Point)**: Retrieves policies from storage.
- **PAP (Policy Administration Point)**: Manages policy creation and updates.

## Installation

```bash
composer require mnaatjes/abac-auth
```

## Quick Start

```php
use mnaatjes\ABAC\ABAC;
use mnaatjes\ABAC\Foundation\PDP;
use mnaatjes\ABAC\Adapters\PolicyManagers\JSONPolicyManager;

// Initialize the Policy Manager
$policyManager = new JSONPolicyManager('path/to/policies.json');

// Initialize the PDP
$pdp = new PDP($policyManager);

// Check access
$decision = $pdp->decide($context);

if ($decision->isPermitted()) {
    // Access granted
} else {
    // Access denied
}
```

## Documentation

Detailed documentation on implementation, testing, and design patterns can be found in the `docs/` directory.

- [Testing Strategy](docs/testing-strategy.md)
- [Design Summary](abac_design_summary.md)
- [Application Lifecycle](docs/ref-application-lifecycle.md)

## Development

### Prerequisites
- PHP 8.1+
- Composer
- Docker (optional, for isolated environment)

### Running Tests
```bash
vendor/bin/phpunit
```

## License

This project is licensed under the MIT License.
