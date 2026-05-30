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

if shared_ui && Dir.exist?(File.join(shared_ui, "assets/controllers"))
  pin_all_from File.join(shared_ui, "assets/controllers"), under: "controllers"
end
