export default {
  format: ['*.{php,js,json}'],
  installDev: [
    {
      git: 'git@github.com:tangibleinc/framework',
      dest: 'vendor/tangible/framework',
      branch: 'main',
    },
  ]
}
