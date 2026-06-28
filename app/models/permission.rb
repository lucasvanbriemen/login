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
      apps:     %i[crud],
      accounts: %i[crud],
      email:    %i[crud],
      github: {
        repositories: %i[crud],
        items:        %i[crud],
        notifications:  %i[crud]
      },
      music: {
        playlists: %i[crud],
        songs:     %i[crud]
      },
      student_portal: %i[crud],
      teacher_portal: %i[crud]

    },
    student: {
      student_portal: %i[crud]
    },
    teacher: {
      teacher_portal: %i[crud]
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
