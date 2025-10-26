# IDE Stub Files

This directory contains stub files to help IDEs (like VS Code with PHP Intelephense) recognize classes and methods that are not directly available in the project.

## Why are these files needed?

The project uses ThinkPHP framework and PHPUnit for testing, but the dependencies are not installed (no composer.lock file and vendor directory is incomplete). Without the actual dependencies, the IDE cannot recognize the classes and methods, resulting in errors like:

- "Undefined type 'Tests\TestCase'"
- "Undefined method 'get'"
- "Undefined method 'post'"
- "Undefined method 'assertNotNull'"
- "Undefined method 'assertEquals'"
- "Undefined method 'createTestUser'"

## What do these files do?

These stub files provide empty implementations of the classes and methods, which is enough for the IDE to recognize them and provide proper code completion and error checking.

## How to use these files?

1. Configure your IDE to include this directory in the project's include path.
2. For VS Code with PHP Intelephense, you can add the following to your settings.json:

```json
{
    "intelephense.files.include": [
        "${workspaceFolder}/**",
        "${workspaceFolder}/.ide-stubs/**"
    ]
}
```

## What's next?

Once you have PHP and Composer installed, run `composer install` to install the actual dependencies. After that, these stub files are no longer needed, but they won't cause any harm if left in place.