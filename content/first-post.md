---
title: First Post
slug: first-post
hero_image: 
  src: /assets/posts/foop.png
  alt: 'Foop'
summary: In this post, I will set up a Symfony 5 website + Tailwind CSS on Heroku and go over some potential pain points in the process. Previously, I was setting up my website using Hugo as an attempt to learn Go, but I never really got around to writing any Go code to further my understanding. Now that I'm interested in continuing my PHP development journey, I can't think of a better framework to use than Symfony for many reasons that I will detail in future posts.
date_created: "2021-12-02"
date_published: "2021-12-02"
date_updated: "2021-12-02"
tags:
  - test
  - example
---

There comes a time in every man's life when he needs to rewrite his personal website. Okay, maybe that's not every 
man's problem, but for most web developers, we tend to attempt a decent web presence in order to appear 
professionally attractive. I am in the process of such an upgrade, and I thought I would write a series of posts 
about the whole ordeal.

In this post, I will set up a Symfony 5 website + Tailwind CSS on Heroku and go over some potential pain points in 
the process. Previously, I was setting up my website using Hugo as an attempt to learn Go, but I never really got 
around to writing any Go code to further my understanding. Now that I'm interested in continuing my PHP development 
journey, I can't think of a better framework to use than Symfony for many reasons that I will detail in future posts.

## Create a Symfony Project

Of course, the first step in this process is to create a basic symfony application. Symfony has [great docs on how to 
start a project](https://symfony.com/doc/current/setup.html), and they recommend using the Symfony CLI which was the 
route I went.

```shell
# Check your requirements first. You need PHP installed and certain extensions.
symfony check:requirements

# Create a full project since you will probably use most of it and get frustrated adding single components.
symfony new my_website --full

# After creating the site, you should put it into version control.
cd my_website && git init

# I added the Encore bundle to integrate Turbo and Stimulus later.
composer require symfony/webpack-encore-bundle  

# And then start up the server in the background.
symfony serve -d
```

After running all of those commands you should see the default Symfony welcome page.

## Deploy Code on Heroku

Heroku is a great platform for easing the burden of devops-like work and letting you focus more closely on the 
application code. With the Heroku addons marketplace, you also get access to test out many services without signing 
up since Heroku SSO auth takes care of it all. My favorite addons are Algolia for search and Cloudinary for media 
handling. You even can use the addons marketplace to set up services for local development in a pinch, and it is a 
breeze to copy resources between Heroku environments. 

I can't say that a lot of PHP applications are deployed on the Heroku platform, but at least they provide an officially 
supported buildpack for PHP vs. a lot of languages that have no official buildpack. In fact, PHP is third in terms 
of recent buildpack deploys with node.js and Ruby coming in first and second, respectively.

Since I use Heroku for work, I already have the CLI installed, but you can follow [instructions to install it on your 
machine](https://devcenter.heroku.com/articles/heroku-cli). Then, Heroku allows you to [create an app via the CLI 
with notes for Symfony](https://devcenter.heroku.com/articles/deploying-symfony4). Later I'll explain a pain point 
with the current Heroku Symfony help article, but the initial steps should work just fine. I actually create apps 
via the Heroku admin UI so don't feel like you have to use the CLI.

### Procfile and app.json

You still need to add a `Procfile` in order for Heroku to know how to serve up your app. My Procfile also contains a 
release script command. If you need later, you can also add a `worker` command to run tasks in the background.

```
release: ./scripts/release-tasks.sh
web: heroku-php-apache2 public/
```

In addition, you can add an `app.json` file in order to specify addons per environment, the buildpacks installed, 
and many other options I never use. I installed three buildpacks to run my Symfony application: php, nodejs, and the 
Heroku CLI. I use the CLI commands sometimes in release scripts to copy resources to review apps, but that is an 
advanced Heroku topic not applicable to setting your app up. 

```json
{
  "buildpacks": [
    {
      "url": "https://github.com/heroku/heroku-buildpack-php"
    },
    {
      "url": "https://github.com/heroku/heroku-buildpack-nodejs"
    },
    {
      "url": "https://github.com/heroku/heroku-buildpack-cli"
    }
  ]
}
```

Now you should have all the configuration in place to deploy your app and work on feature development...However, it's 
not that easy, and I bet you'll have several more steps to figure out before declaring your deployment workflow 
successful.

## GitHub Integration and Review Apps

I strongly recommend using Heroku's GitHub integration to link your Heroku app to your codebase. Of course, this 
only works if you host your code on GitHub, but you can create a free mirror to use the feature even if you manage 
development of the code elsewhere.

![image of GH integration screen in Heroku]

The biggest advantage to using GitHub integration over the Heroku CLI, IMHO, is the automatic creation of Review App 
environments for pull requests. With the CLI you would have to create each review app and then push the branch to 
the environment...and then QA your update. 

With the GitHub integration, you can get a notification in Slack with a link to the deployment when it's ready, and 
you never have to type out any commands or use an admin UI. I'll take that bargain any day. 

![image of turning on review apps]

## Adding Tailwind CSS and Running NPM Scripts

I don't want to get too far into the particulars of my website plans in this post, but I will mention installing 
Tailwind CSS just to go over issues you might encounter with npm/yarn and build steps. 

```shell
# Add Tailwind to package.json
yarn add -D tailwindcss@latest postcss@latest autoprefixer@latest 

# Create postcss.config.js and tailwind.config.js configuration files
npx tailwind init
```

After this, you need to edit the Post CSS config file. You can also edit the Tailwind config file, but you don't 
have to do that now. By the time you get to production, you'll want to purge your template files, though, or else 
your users will be downloading much more CSS than they need to render your site properly.

```js
// postcss.config.js
module.exports = {
  plugins: {
    tailwindcss: {},
    autoprefixer: {},
  }
}
```

Now, you're still not done as Webpack Encore doesn't load Post CSS by default. You'll need to add a line to the 
webpack configration file.

```js
// ...in webpack.config.js  
// Add for Tailwind integration.
Encore.enablePostCssLoader();
```

After that config change, I still wasn't done as another dependency needed installing. The command line output will 
tell you to install a specific version of `postcss-loader` but I couldn't pin it to the version listed, so I simply 
installed the default version.

```shell
# Does not find the specific version
yarn add postcss-loader@^6.0.0 --dev

# Works fine.
yarn add postcss-loader --dev
```

Finally, I could use Tailwind after including the directives in `assets/styles/app.css` and any CSS I wanted 
to `@apply` globally and group for common CSS utility classes.

## Heroku Build Step Problems

Having all the dependencies installed I wanted to complete my initial development and deployment cycle, I turned to 
making Heroku look like what I saw locally via `symfony serve -d`. 

Initially, Heroku ignored the nodejs buildpack even though I included it in `app.json`. Heroku also claims that if 
you have a `package.json` file at the root of your project, it will automagically pick that up, install dependencies,
and run the build command, if it exists.

![image of Heroku UI add buildpack]

You can try adding the buildpack via the UI, if you don't see it listed in the app's settings section. After doing 
this...and maybe something else...`yarn install` and `yarn build` became part of the slug build process. But what to 
do with assets being built during development?

The site I'm setting up encourages use of a dev server watching for file changes, and I'm not embarrassed to admit 
that I was trying to run the dev server on Heroku production before encountering these problems and finding 
solutions. I think you might be in the same boat, and hopefully this information helps to lessen your head scratching.

The obvious first thought is to look for a build command in the scripts section of `package.json`. In this case, the 
Encore bundle already adds a build command, and that should just work...but after looking at a deployment and seeing 
broken CSS, I wondered if the build command really worked. 

To debug, I removed purging Twig template files from the build step and the CSS returned to normal. True, I'm now 
forcing people to download 3 MB of CSS they don't need, but I'll save that battle for another day.

## 404 Errors

After seeing my homepage index route show up on Heroku, finally, finally I must be done right? Nope. Click any link 
and end up with a sad 404 page. I knew it wasn't the Symfony app reporting the 404 and the scant white error page 
reminded me of mis-configured Apache webservers back in my MAMP days.

Luckily for us, Symfony created a specific buildpack just for this issue. It adds an .htaccess file to your public 
directory so Apache can route the request to the `index.php` front controller.

```shell
composer require symfony/apache-pack
```

After deploying that change, then you really should be done with your task of setting up a Symfony 5 application on 
Heroku with Tailwind and Encore to boot.

## GitHub Actions to keep the dyno fresh

One issue with using the free tier of Heroku for hosting a personal app is the cold start problem. After 30 minutes 
of inactivity, Heroku will put your dyno to sleep, and the next request requires several seconds of bootup time. For 
a personal site, I can handle this problem, and if I really wanted to continue using Heroku and had higher traffic 
loads, I would probably just pay for a hobby-tier dyno and call it a day...or not use Heroku.

But for extra credit, you can actually use GitHub Actions minutes to ping your Heroku app every 20 minutes so it 
never goes to sleep. Initially, I thought the monthly dyno hour limitations would be an issue and I'd run out of 
them before filling 30 days of constant activity. 

However, you can [get up to 1000 hours per month](https://devcenter.heroku.com/articles/free-dyno-hours) 
which equates to 33.3 hrs per day meaning you can have the dyno running all the time with this trick.

...but you need to figure this out...or I will do so and comment on that idea in a future post. Pick your poison.

