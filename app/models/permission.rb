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
#   Permission.for_app("admin", :github)     # => the github subtree
#   Permission.flat_for("admin")             # => [:manage_apps, :create_items, ...]
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

  # One application's (or area's) subtree, e.g. for_app("admin", :github).
  def self.for_app(role, app)
    self.for(role).fetch(app, {})
  end

  # Flat list of every permission a role has, regardless of nesting depth.
  def self.flat_for(role)
    flatten(self.for(role))
  end

  # Recursively collect permission symbols from a (possibly nested) group.
  def self.flatten(group)
    case group
    when Array then group
    when Hash  then group.values.flat_map { |v| flatten(v) }
    else []
    end
  end
end
