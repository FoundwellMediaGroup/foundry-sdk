# Architecture

The Foundwell SDK is the stable client boundary between Foundwell products and the Foundwell Platform.

## Layers

1. **Product code** uses capability services such as Licensing or Updates.
2. **SDK services** convert product intent into Platform API requests.
3. **HttpClient** applies common authentication, serialization, retries, logging, and error mapping.
4. **TransportInterface** performs the actual network operation. `CurlTransport` is the production implementation.

Products should not depend directly on cURL or Platform endpoint details. The public service contracts remain stable while the transport and API implementation may evolve.

## Retry policy

Only transient connection failures and HTTP 408, 425, 429, 500, 502, 503, and 504 responses are retried. Authentication and authorization failures are never retried automatically.

## Security

Platform communication requires HTTPS. API keys are sent only in the Authorization header and must never be written to logs.
