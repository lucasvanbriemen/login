# Defines the roles an Account can have and the permissions each role grants,
# grouped by the application the permission applies to.
#
# This is a plain Ruby class (not an ActiveRecord model) — roles live in the
# `accounts.role` string column, and the mapping below is the single source of
# truth for what each role is allowed to do.
#
# Usage:
#   Permission.for("admin")            # => { email: [:access_email], ... }
#   Permission.for_app("admin", :email) # => [:access_email]
#   Permission.flat_for("admin")       # => [:manage_apps, :access_email, ...]
class Permission
  # Map each role to its permissions, grouped by application.
  # Add a capability by adding its symbol under the relevant app;
  # add an app by adding a key to a role; add a role by adding a top-level key.
  ROLES = {
    admin: {
      apps:          %i[manage_apps],
      accounts:      %i[manage_accounts],
      email:         %i[access_email],
      repositories:  %i[access_private_repositories],
      items:         %i[create_items update_items],
      notifications: %i[view_notifications]
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

  # All permissions for a role, grouped by app.
  # Accepts the `role` string from the accounts table (e.g. "admin").
  # Blank roles fall back to DEFAULT_ROLE; unknown roles get {}.
  def self.for(role)
    ROLES.fetch(role.presence&.to_sym || DEFAULT_ROLE, {})
  end

  # Just one application's permissions, e.g. for_app("admin", :email).
  def self.for_app(role, app)
    self.for(role).fetch(app, [])
  end

  # Flat list of every permission a role has, ignoring grouping.
  def self.flat_for(role)
    self.for(role).values.flatten
  end
end
