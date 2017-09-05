# PHP Shutterstock API v2 Example

This is a demo application that illustrates how to integrate with the Shutterstock API, perform OAuth handshakes, and allow purchases to flow through.

## Installation

First, you'll need to use [Composer](https://getcomposer.org/) to install the dependencies.

```bash
$ composer install
```

After composer is done, go into `lib/client.php` and updated with your clientId and clientSecret, which you can request on the [Shutterstock Developers site](https://developers.shutterstock.com/).

Finally, start the built-in PHP server (>= 5.4) and open the site (with port) in a browser.

```bash
$ php -S localhost:8000
```

## Concepts

This demo packages is split into three core pieces to better explain how things work. All requests are done through PHP, triggered by ajax requests from the core page.

### Search

Category, search, and image detail requests do not require special authentication. These requests are handled by basic auth with your client id and secret.

### OAuth

This demo utilizes a popup to allow Shutterstock customers to login to their account and grant access to the application. While the initial authentication is performed by basic auth, the subsequent token requests are handled by typical OAuth2 flow. Read more about this flow on the [API Guide](https://developers.shutterstock.com/guides/authentication#oauth-2-0).

### Purchases

Subscription fetching, licensing, and downloads all require a registered token with the proper scopes. These endpoints should only be called after the OAuth handshake is complete and a client token has been granted.

## License

[Apache 2.0](LICENSE) © 2016-2017 Shutterstock Images, LLC