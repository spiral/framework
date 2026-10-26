# Contributing 

Feel free to contribute to the development of the Framework or its components.

For more information on contributing rules you can find on the documentation 
page at https://spiral.dev/docs/about-contributing

## Pull request titles

PRs are squash-merged, and the PR title becomes the commit subject that drives releases and the changelog.
Use [Conventional Commits](https://www.conventionalcommits.org/): `<type>(<scope>): <subject>`.

- Types that appear in the changelog: `feat` (minor release), `fix`, `perf`, `deps`, `revert` (patch release).
  Other types (`docs`, `refactor`, `test`, `build`, `ci`, `chore`, `style`) are hidden.
- The scope is optional but recommended: the component name in lower case, e.g. `fix(router): ...`.
- No breaking markers (`feat!:`, `BREAKING CHANGE:`): they would trigger a major release.
