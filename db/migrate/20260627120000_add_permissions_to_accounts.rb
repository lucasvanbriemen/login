class AddPermissionsToAccounts < ActiveRecord::Migration[8.0]
  def change
    add_column :accounts, :role, :string, null: false, default: ""
  end
end
