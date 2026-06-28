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
    ],
    member: %i[
    ],
    unknown: %i[]
  }.freeze

  # Role assigned when the `role` column is blank (the schema default is "").
  DEFAULT_ROLE = :unknown

  def self.for(role)
    ROLES.fetch(role.presence&.to_sym || DEFAULT_ROLE)
  end
end
