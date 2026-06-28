class Account < ApplicationRecord
  has_secure_password

  has_many :tokens, dependent: :destroy

  def permissions
    Permission.for(self).permissions
  end
end
