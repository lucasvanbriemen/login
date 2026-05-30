class SessionsController < ActionController::Base
  def new
    render plain: "login"
  end
end
