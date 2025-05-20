(function() {
  // Access the localized data
  let pluginRows = window.tangibleUpdaterPluginRowEnqueued
  if (!pluginRows) return

  // Ensure array
  pluginRows = Array.isArray(pluginRows) ? pluginRows : pluginRows.split(',')

  // Add update class to each plugin row
  for (const file of pluginRows) {
    const el = document.querySelector(`tr[data-plugin="${ file }"]`)
    el?.classList.add('update')
  }
})()
