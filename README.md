# Proyecto 1: Symfony rebuild of an old UCV puzzle

This repo is me going back to something that had been bothering me for a very long time.

In 2010, while I was a student at UJAP, I used to spend part of my holidays looking at assignments from the Universidad Central de Venezuela, Facultad de Ciencias, Escuela de Computacion. I thought their programming assignments were more interesting than the ones I had around me, so I would try to build some of them for fun.

This puzzle was one of those projects. I tried to make it in Java Swing and got close, but I never finished it because of a bug I could not explain back then. The first images behaved fine, then later ones started acting strangely, and that was the end of it for years.

This repository is me taking another shot at the same problem, but this time as a Symfony web application.

## What this project is

The application models a seven-dial puzzle. Each dial can be in one of four positions, represented by the original image states from the Java version.

The initial state is:

```text
[2, 0, 1, 3, 2, 1, 3]
```

The goal is to reach:

```text
[0, 0, 0, 0, 0, 0, 0]
```

The app supports two movement rules:

- `Joint` mode: clicking a dial rotates that dial and every dial to its right.
- `Individual` mode: clicking a dial rotates only that dial.

The web app lets you:

- play the puzzle manually
- switch between the two rule sets
- reset the board to the original layout
- see the minimum number of moves to solve the current board
- see how many shortest solutions exist
- inspect one example of an optimal move sequence

## Why I came back to it

Part of it was honestly unfinished business.

I remembered the bug very clearly: the first images moved correctly, the second one too, and then from the third onward things started getting weird. Coming back to it now, the bug turned out not to be in the puzzle itself, but in my event handling. The old Swing version triggered click handlers recursively, so the dials later in the row were getting updated more than once per move.

The other reason is that this kind of project is actually useful as portfolio material:

- it has a clear domain model
- it has visible interactive behavior
- it benefits from tests
- it has a real debugging story behind it
- it mixes user interface work with actual algorithmic reasoning

Also, it is just more fun to show a project like this than yet another CRUD app with fake users and fake invoices.

## Computer science ideas in it

This puzzle is small, but there is real CS hiding inside it.

### 1. State space modeling

The board is a finite state machine.

- There are `7` dials.
- Each dial has `4` possible positions.
- That gives a total state space of `4^7 = 16,384` possible board configurations.

Each move transforms one state into another according to the selected rule set.

### 2. Graph theory

You can view the puzzle as a graph:

- each node is a board configuration
- each edge is a legal move

Solving the puzzle means finding a path from the initial node to the solved node.

### 3. Breadth-first search for shortest paths

Every move has the same cost, so breadth-first search is the right tool to guarantee an optimal solution.

The solver in [src/Puzzle/PuzzleSolver.php](./src/Puzzle/PuzzleSolver.php) runs a BFS over the puzzle graph and returns:

- the minimum number of moves
- the number of shortest solutions
- one example shortest path

For the original layout used in this project, the solver finds:

- `Joint` mode: `13` minimum moves
- `Individual` mode: `12` minimum moves

### 4. Counting shortest solutions

Finding one shortest path is one problem. Counting how many shortest paths exist is another.

This implementation keeps track of:

- the first depth where each state is discovered
- how many ways that state can be reached at that same minimum depth

That is a standard shortest-path counting technique layered on top of BFS.

One important note: the reported number counts shortest move sequences, not equivalence classes of "strategies". If two sequences differ only by the order of independent moves, they are still counted separately.

### 5. Modular arithmetic

Each dial rotates through four positions, so updates naturally happen modulo `4`.

That means each move is effectively adding `1 (mod 4)` to either:

- one dial, in `Individual` mode
- a suffix of dials, in `Joint` mode

### 6. Separation of concerns

I wanted the rebuild to stay honest, so the puzzle rules are not buried inside controllers or templates.

- [src/Puzzle/PuzzleState.php](./src/Puzzle/PuzzleState.php) models the board and move rules
- [src/Puzzle/PuzzleMode.php](./src/Puzzle/PuzzleMode.php) models the two movement modes
- [src/Puzzle/PuzzleSolver.php](./src/Puzzle/PuzzleSolver.php) handles optimal-solution analysis
- [src/Controller/PuzzleController.php](./src/Controller/PuzzleController.php) handles HTTP requests and session state
- [templates/puzzle/index.html.twig](./templates/puzzle/index.html.twig) renders the UI

That keeps the core logic testable without depending on the web layer.

## What changed from the Java version

The old Java version was basically me trying to brute-force the UI and logic together inside Swing event handlers.

This rebuild is different in a few important ways:

- the puzzle state is modeled explicitly instead of being hidden inside UI callbacks
- the move rules live in domain classes instead of in button click code
- the solver computes optimal solutions using BFS
- the app has unit tests, request-level tests, and real browser tests
- the browser interaction is smoother, but still keeps a normal non-JavaScript fallback

Most importantly, I now understand the original bug.

The strange image behavior in the Java version happened because one click handler called the next one recursively, and those handlers called others again. So some dials, especially later ones, were effectively rotating multiple times per click.

## Tech stack

- PHP 8.4
- Symfony 8
- Twig
- PHPUnit
- Symfony Panther

## Running the project

Install dependencies:

```bash
composer install
```

Start the local server:

```bash
symfony server:start
```

Then open:

```text
http://127.0.0.1:8000/
```

## Running the tests

```bash
php bin/phpunit
```

This project includes three test layers:

- unit tests for the puzzle domain
- Symfony functional tests for request and session behavior
- Panther browser tests for the JavaScript-enhanced puzzle interactions

### Browser test prerequisites

The Panther suite uses Google Chrome plus ChromeDriver.

Install the matching driver with:

```bash
composer require --dev dbrekelmans/bdi
vendor/bin/bdi detect drivers
```

On this project, the detected driver is stored in:

```text
drivers/chromedriver
```

If Chrome is installed in the standard macOS location, the bootstrap will detect it automatically.

Useful checks:

```bash
php bin/console lint:twig templates
php bin/console lint:container
php bin/console debug:router
```

## Continuous integration

GitHub Actions is configured in [`.github/workflows/ci.yml`](./.github/workflows/ci.yml).

The workflow runs on every push and pull request and covers:

- dependency installation
- Twig and container linting
- the full PHPUnit suite
- Panther browser tests with Chrome

When a browser test fails in CI, Panther screenshots are uploaded as build artifacts to make debugging easier.

## Provenance

This project is a personal rebuild based on `proyecto_1.pdf` from old UCV course material and on my own unfinished Java attempt from 2010.

It is not an official UCV project and should be understood as a portfolio rebuild inspired by that original assignment.

## Personal note

I like this project because it is a very specific kind of unfinished work. It was not some impossible research problem. It was just one bug that I could not reason about properly at the time.

Rebuilding it now as a web app felt like part debugging exercise, part nostalgia trip, and part reminder that sometimes the difference between "I almost had it" and "I actually solved it" is just ten or fifteen years of experience.
