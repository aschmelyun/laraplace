# Laraplace

This is the source code for the finished app in the "[I built r/place with PHP and JavaScript in an hour](https://www.youtube.com/watch?v=XSw5fFo0_pA)" YouTube video. If you find any problems with the code, or need help with this project in any way, feel free to open an [issue](https://github.com/aschmelyun/laraplace/issues/new) or reach out to me on [Twitter](https://twitter.com/aschmelyun)!

## Installation

Clone this repo, and then run the following locally:

```bash
cp .env.example .env
composer install
npm install
php artisan serve
php artisan websockets:serve
```

You will need a MySQL and Redis database service set up and connected. See the .env.example file for more details!

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
