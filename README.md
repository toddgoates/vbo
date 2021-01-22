# Virtual Box Office

Virtual Box Office (VBO) is an application where users can create listings for events such as plays, concerts, movies, or talent shows. Guests can view event listings and purchase tickets.

This application is to practice concepts learned in Adam Wathan's [Test Driven Laravel](https://course.testdrivenlaravel.com/) Course.

## Getting Started

First, ensure that Docker is installed on your computer. If it isn't, you can download it here: [https://www.docker.com/get-started](https://www.docker.com/get-started)

Next, clone this repo onto your computer. To build your Docker image, run this command from the root directory of the project:

```shell
docker-compose up --build
```

Once your Docker container is running, you will need to install Composer dependencies. Run this command from the root directory:

```shell
./container composer install
```

**Note**: this project includes a bash script that lets you run commands from outside of your container. Check out the `container` script at the root directory of the project.

Once your dependencies are installed, you will need to create a `.env` file. Copy the .env.example file like this:

```shell
cp .env.example .env
```

**Note**: the .env.example file has been set to use 'sqlite' for the Database Connection. This is the best option to start out with when doing Test Driven Development. When you run your tests, you're using an in-memory database. See `phpunit.xml` for more info.

Now you need to generate a Laravel Application key. This can be done with the following command:

```shell
./container php artisan key:generate
```

Your local development environment is now ready to go!

## Running Tests

To run your testing suite, use the following command:

```shell
./container php artisan test
```
