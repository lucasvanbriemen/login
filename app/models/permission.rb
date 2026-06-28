# Defines the roles an Account can have and the permissions each role grants,
# grouped by the application (and sub-area) the permission applies to.
#
# This is a plain Ruby class (not an ActiveRecord model) — roles live in the
# `accounts.role` string column, and the mapping below is the single source of
# truth for what each role is allowed to do.
#
# Groups can nest arbitrarily deep. Leaves are arrays of permission symbols.
#
# Usage:
#   Permission.for("admin")                  # => full nested hash
class Permission
  # Map each role to its permissions, grouped by application and sub-area.
  # Add a capability by adding its symbol to the relevant leaf array;
  # add a group by nesting another hash; add a role via a new top-level key.
  ROLES = {
    admin: {
      apps:     %i[manage_apps],
      accounts: %i[manage_accounts],
      email:    %i[access_email],
      github: {
        repositories: {
          access:        %i[access_private_repositories],
          items:         %i[create_items update_items],
          notifications: %i[view_notifications]
        }
      },
      music: {
        playlists: %i[create_playlists update_playlists],
        songs:     %i[create_songs update_songs]
      }
    },
    student: {
      dashboard: %i[access_student_dashboard]
    },
    teacher: {
      dashboard: %i[access_teacher_dashboard]
    },
    unknown: {}
  }.freeze

  # Role assigned when the `role` column is blank (the schema default is "").
  DEFAULT_ROLE = :unknown

  # All permissions for a role, grouped (nested) by app/area.
  # Accepts the `role` string from the accounts table (e.g. "admin").
  # Blank roles fall back to DEFAULT_ROLE; unknown roles get {}.
  def self.for(role)
    ROLES.fetch(role.presence&.to_sym || DEFAULT_ROLE, {})
  end
end
