# Project Title

One Paragraph of project description goes here

## Initial Development Setup

This project can be run locally with Vagrant. It was provisioned with [Rokanan](https://github.com/fostermadeco/rokanan). See that repo for more about the necessary requirements.

To run this project after cloning:

```
vagrant up
```

Then install the dependencies:
```
vagrant ssh
composer install
npm install
```

or on the host:
```
rokanan run "composer install"
rokanan run "npm install"
```

## Asset Task Usage

This project uses [Mix](https://laravel.com/docs/master/mix) for build process.

NOTE: All tasks and commands should be run on the Vagrant box.

**Development Task**

Creates static files in `/public/assets/`:
```
npm run dev
```

**Watch Task**

Uses BrowserSync to refresh assets and reload browser:
```
npm run watch
```

View the site at [http://localhost:3000/](http://localhost:3000/) or the proxy as set in `webpack.mix.js`, e.g. [http://fm-example.test:3000](http://fm-example.test:3000)

**Production Task:**
```
npm run production
```

## Deployment

Add additional notes about how to deploy this on a live system
