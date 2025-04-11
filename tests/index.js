import path from 'node:path'
import fs from 'node:fs/promises'
import { test, is, ok, run } from 'testra'

run(async () => {
  const cwd = process.cwd()
  const pkg = JSON.parse(
    await fs.readFile(path.join(cwd, '.wp-env.json'), 'utf8'),
  )
  const { testsPort: port } = pkg
  const siteUrl = `http://localhost:${port}`

  test('Site with updater', async () => {

    let result = await fetch(siteUrl)

    ok(result, 'responds')
    console.log(result)

  })
})
