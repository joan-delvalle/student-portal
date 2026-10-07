# ADR 0001: Backend Framework

## Status

Accepted

## Date

2026-10-07

## Context

The Student Portal needs a backend API that can provide student information and connect the different features of the system. The backend should be simple to develop, easy to test, and able to provide REST API endpoints.

## Decision

We chose **Node.js with Express.js** as the backend framework for the Student Portal.

The backend uses Express.js to create REST API endpoints under `/api/v1`. Swagger UI is also used to document and test the API.

The backend follows this structure:

- Routes handle API requests.
- Services contain the application logic.
- Data files contain the mock data.

## Reasons

We selected Express.js because:

1. It is simple and beginner-friendly.
2. It works well with JavaScript.
3. It is suitable for REST APIs.
4. It has many available packages.
5. It is easy to test using Swagger UI.
6. It can be connected to a database later.

## Consequences

### Positive

- Easy to understand and maintain.
- Simple REST API development.
- Easy to test using Swagger.
- Can be expanded when the system needs a database.
- Other modules can connect to the API.

### Negative

- The current backend uses mock data.
- Authentication is not yet implemented.
- Data will not be permanently saved until a database is added.

## Alternatives Considered

### FastAPI

FastAPI was considered because it is good for building APIs. However, Express.js was selected because our project uses JavaScript and Express is easier for our group to work with.

### Django

Django was also considered, but it was not selected because it provides more features than we currently need for this Student Portal API.

## Conclusion

Node.js with Express.js is the selected backend framework for the Student Portal because it is simple, flexible, and suitable for creating the REST API required by the project.