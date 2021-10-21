# Job Offers

This project is a CLI application created to take the Job Offers posted at Leeway Academy and send them to interested recipients.

## Dependencies

The project has four main dependencies:

* An online spreadsheet provider
* An email marketing platform
* A template renderer library
* [Docker](https://www.docker.com/)

In this implementation the dependencies are met via Google SpreadSheets, [MailChimp](https://mailchimp.com) and [Twig](https://twig.symfony.com/) respectively.

## Installation

1. Make sure you have docker installed and can execute it.
2. Copy the file `.env.sample` into `.env.local` and set the values according to your needs.
3. Run `make install`