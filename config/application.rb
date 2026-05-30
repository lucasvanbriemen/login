require_relative "boot"

require "rails/all"

# Require the gems listed in Gemfile, including any gems
# you've limited to :test, :development, or :production.
Bundler.require(*Rails.groups)

module LoginRuby
  class Application < Rails::Application
    # Initialize configuration defaults for originally generated Rails version.
    config.load_defaults 8.0

    # Please, add to the `ignore` list any other `lib` subdirectories that do
    # not contain `.rb` files, or that should not be reloaded or eager loaded.
    # Common ones are `templates`, `generators`, or `middleware`, for example.
    config.autoload_lib(ignore: %w[assets tasks])

    # Configuration for the application, engines, and railties goes here.
    #
    # These settings can be overridden in specific environments using the files
    # in config/environments, which are processed later.
    #
    # config.time_zone = "Central Time (US & Canada)"
    # config.eager_load_paths << Rails.root.join("extras")

    # --- Shared UI (centralized helpers/partials/assets across projects) ---
    # One directory on disk that every app reads from. No gem/bundle step:
    # edit the source once, restart the apps, all of them pick it up.
    # Override the location with SHARED_UI_PATH; otherwise auto-discover.
    shared_ui = ENV.fetch("SHARED_UI_PATH") do
      [
        "/var/www/vhosts/ltvb.nl/shared-ui",         # server
        File.expand_path("../../shared-ui", __dir__) # local checkout next to the app
      ].find { |path| Dir.exist?(path) }
    end

    if shared_ui && Dir.exist?(shared_ui)
      config.paths["app/views"]   << File.join(shared_ui, "views")
      config.paths["app/helpers"] << File.join(shared_ui, "helpers")
      config.assets.paths         << File.join(shared_ui, "assets")
    end
  end
end
