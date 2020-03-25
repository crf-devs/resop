module.exports = {
  parser: 'babel-eslint',
  extends: [
    'plugin:prettier/recommended',
    'eslint:recommended',
  ],
  env: {
    amd: true,
    browser: true,
  },
  rules: {
    'max-len': ['error', {
      code: 250,
      ignoreUrls: true,
      ignoreComments: false,
      ignoreRegExpLiterals: true,
      ignoreStrings: false,
      ignoreTemplateLiterals: false,
    }],
  },
};
