# Create a fork

If you want to contibute, please create a [fork](https://help.github.com/en/github/getting-started-with-github/fork-a-repo) of the project

# Pull request description

Please provide `Closes #xxx` inside the PR description, where xxx is the number of the issue.
If your pull request is in a `work in progress` state, please prefix it with `[WIP]`.

# Before commiting

Please always run the following commands before commiting, or the CI won't be happy ;-)

```bash
make fix-cs
make test
```

Hint: you can run `make fix-cs-php` instead of `make fix-cs` if you are one of those backend devs who don't touch any css or js file.
