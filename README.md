# API OAuth2 Authentication with Silex

This project demonstrates the implementation of OAuth2 authentication for API endpoints using the Silex micro-framework. It provides a secure and reliable way to authenticate API requests using OAuth2 access tokens.

## Features

- API endpoint authentication via OAuth2 access tokens
- Access token generation and validation
- Secure handling of client credentials
- Easy integration with existing Silex API applications

## Usage

1. Start the PHP built-in web server.

```bash
php -S localhost:8000 -t public
```

2. Access the API endpoints by sending HTTP requests with the appropriate authentication headers.

   - For client credentials grant type:

     ```
     POST /oauth2/token HTTP/1.1
     Host: localhost:8000
     Content-Type: application/x-www-form-urlencoded
     
     grant_type=client_credentials&client_id=YOUR_CLIENT_ID&client_secret=YOUR_CLIENT_SECRET
     ```

     This will return an access token and other details.

   - For protected API endpoints, include the access token in the `Authorization` header:

     ```
     GET /api/protected-endpoint HTTP/1.1
     Host: localhost:8000
     Authorization: Bearer ACCESS_TOKEN
     ```

     Replace `ACCESS_TOKEN` with the obtained access token.

3. Validate the access token and handle the API request in your application's endpoint handlers.
