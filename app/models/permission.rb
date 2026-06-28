# Defines the roles an Account can have and the permissions each role grants,
# grouped by the application (and sub-area) the permission applies to.
#
# This is a plain Ruby class (not an ActiveRecord model) — roles live in the
# `accounts.role` string column.
#
# Structure:
#   * BASE defines every app/area once. It is the default (what `unknown` gets).
#   * Each entry in ROLE_PERMISSIONS only declares what that role OVERRIDES;
#     it is deep-merged onto BASE, so new apps only need to be added to BASE.
#   * Each leaf is a list of allowed operations. CRUD is shorthand for all four.
#     Partial access is just a subset, e.g. %i[read] or %i[create read].
#
# Usage:
#   Permission.for("admin")                                  # => full nested hash
class Permission
  # The four operations. Use CRUD as shorthand for "full access" on a leaf.
  CRUD = %i[create read update delete].freeze

  # Every app/area, listed once. Values are the DEFAULT operations (none here).
  BASE = {
    apps:     [],
    accounts: [],
    email:    [],
    github: {
      repositories:         %i[read],
      private_repositories: [],
      items:                %i[read],
      notifications:        []
    },
    music: {
      playlists: [],
      songs:     []
    },
    student_portal: [],
    teacher_portal: []
  }.freeze

  # Per-role overrides, deep-merged onto BASE. Only list what differs.
  ROLE_PERMISSIONS = {
    admin: {
      apps:     CRUD,
      accounts: CRUD,
      email:    CRUD,
      github: {
        repositories:         CRUD,
        private_repositories: CRUD,
        items:                CRUD,
        notifications:        CRUD
      },
      music: {
        playlists: CRUD,
        songs:     CRUD
      },
      student_portal: CRUD,
      teacher_portal: CRUD
    },
    student: {
      student_portal: CRUD
    },
    teacher: {
      teacher_portal: CRUD
    },
    unknown: {}
  }.freeze

  # Role used when the `role` column is blank (the schema default is "").
  DEFAULT_ROLE = :unknown

  # Full permission tree for a role: BASE with the role's overrides applied.
  # Accepts the `role` string from the accounts table (e.g. "admin").
  def self.for(role)
    overrides = ROLE_PERMISSIONS.fetch(role.presence&.to_sym || DEFAULT_ROLE, {})
    BASE.deep_merge(overrides)
  end
end
