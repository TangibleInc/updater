export default {
  format: [
    '*.{php,js,json}',
    'includes',
    '!includes/plugin-update-checker', // Third-party
  ],
  installDev: [
    {
      git: 'git@github.com:tangibleinc/framework',
      dest: 'vendor/tangible/framework',
      branch: 'main',
    },
  ]
}
