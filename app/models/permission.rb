# Defines the roles an Account can have and the permissions each role grants.
#
# This is a plain Ruby class (not an ActiveRecord model) — roles live in the
# `accounts.role` string column, and the mapping below is the single source of
# truth for what each role is allowed to do.
#
# Usage:
#   Permission.for(account).can?(:manage_accounts)  # => true / false
#   Permission.roles                                # => [:admin, :member, :guest]
#   account.role                                    # => "admin"
class Permission
  # Map each role to the set of permissions it grants.
  # Add a new capability by adding its symbol to the relevant role(s);
  # add a new role by adding a new key here.
  ROLES = {
    admin: %i[
      manage_accounts
      manage_roles
      view_dashboard
    ],
    member: %i[
      view_dashboard
    ],
    guest: %i[]
  }.freeze

  # Role assigned when the `role` column is blank (the schema default is "").
  DEFAULT_ROLE = :guest

  attr_reader :role

  # Build a Permission for an account (or any object responding to #role).
  def self.for(account)
    new(account.role)
  end

  # All defined role names, e.g. [:admin, :member, :guest].
  def self.roles
    ROLES.keys
  end

  def initialize(role)
    @role = role.present? ? role.to_sym : DEFAULT_ROLE
  end

  # True if this role grants the given permission.
  def can?(permission)
    permissions.include?(permission)
  end

  # All permissions granted to this role (empty for unknown roles).
  def permissions
    ROLES.fetch(role, [])
  end
end
