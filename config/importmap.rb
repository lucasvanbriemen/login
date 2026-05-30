# Pin npm packages by running ./bin/importmap

pin "application"
pin "@hotwired/turbo-rails", to: "turbo.min.js"
pin "@hotwired/stimulus", to: "stimulus.min.js"
pin "@hotwired/stimulus-loading", to: "stimulus-loading.js"
pin_all_from "app/javascript/controllers", under: "controllers"

# Shared UI Stimulus controllers (same directory auto-discovered in
# config/application.rb). Pinned under "controllers" so eagerLoadControllersFrom
# picks them up alongside the app's own controllers.
shared_ui = ENV.fetch("SHARED_UI_PATH") do
  [
    "/var/www/vhosts/ltvb.nl/ui-components",         # server
    File.expand_path("../../ui-components", __dir__) # local checkout next to the app
  ].find { |path| Dir.exist?(path) }
end

# Shared JS modules live at <shared>/assets/js/**. Propshaft's asset root is
# <shared>/assets, so their logical paths keep the "js/" prefix (e.g.
# "js/objects/theme.js"). pin_all_from derives the asset path relative to the
# pinned dir, which wouldn't match that prefix — so pin them under a clean
# "objects" namespace with an explicit `to:` pointing at the real logical path.
if shared_ui && Dir.exist?(File.join(shared_ui, "assets/js/objects"))
  Dir.glob(File.join(shared_ui, "assets/js/objects/*.js")).each do |file|
    name = File.basename(file, ".js")
    pin "objects/#{name}", to: "js/objects/#{name}.js"
  end
end
