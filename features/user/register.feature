Feature:
    In order to fill in my availabilities,
    As a user,
    I must be able to create my account.

    Scenario: As authenticated user, I cannot create an account
        Given I am authenticated as "john.doe@resop.com"
        When I go to "/user/new"
        Then I should be on "/"
        And the response status code should be 200

    Scenario: As anonymous, I cannot create an account with already registered email address
        Given I am on "/user/new"
        When I fill in the following:
            | user[identificationNumber] | 999999A            |
            | user[firstName]            | John               |
            | user[lastName]             | DOE                |
            | user[emailAddress]         | john.doe@resop.com |
            | user[phoneNumber]          | 0612345678         |
        And I select "UL 01-02" from "user[organization]"
        And I check "Maraudeur.se"
        And I press "Valider"
        Then I should be on "/user/new"
        And the response status code should be 400
        And I should see "Cette valeur est déjà utilisée."

    Scenario: As anonymous, I cannot create an account with already registered identification number
        Given I am on "/user/new"
        When I fill in the following:
            | user[identificationNumber] | 990001A            |
            | user[firstName]            | John               |
            | user[lastName]             | DOE                |
            | user[emailAddress]         | new.user@resop.com |
            | user[phoneNumber]          | 0612345678         |
        And I select "UL 01-02" from "user[organization]"
        And I check "Maraudeur.se"
        And I press "Valider"
        Then I should be on "/user/new"
        And the response status code should be 400
        And I should see "Cette valeur est déjà utilisée."

    Scenario: As anonymous, I can create an account with valid data
        Given I am on "/user/new"
        When I fill in the following:
            | user[identificationNumber] | 999999A                      |
            | user[firstName]            | Archibald                    |
            | user[lastName]             | HADDOCK                      |
            | user[emailAddress]         | archibald.haddockr@resop.com |
            | user[phoneNumber]          | 0612345678                   |
        And I select "UL 01-02" from "user[organization]"
        And I check "Maraudeur.se"
        And I press "Valider"
        Then the response status code should be 200
        And I should be on "/"
        And the response status code should be 200
        And I should see "Votre compte utilisateur a été créé avec succès."
        And I should see "Bienvenue, Archibald HADDOCK"
        And I should see "NIVOL : 999999A"

    Scenario: As anonymous, I cannot create an account with empty data
        Given I am on "/user/new"
        When I press "Valider"
        Then I should be on "/user/new"
        And the response status code should be 400
        And I should see "Cette valeur ne doit pas être vide." in the "label[for=user_identificationNumber] .form-error-message" element
        And I should see "Cette valeur ne doit pas être nulle." in the "label[for=user_organization] .form-error-message" element
        And I should see "Cette valeur ne doit pas être vide." in the "label[for=user_firstName] .form-error-message" element
        And I should see "Cette valeur ne doit pas être vide." in the "label[for=user_lastName] .form-error-message" element
        And I should see "Cette valeur ne doit pas être vide." in the "label[for=user_emailAddress] .form-error-message" element
        And I should see "Cette valeur ne doit pas être nulle." in the "label[for=user_phoneNumber] .form-error-message" element
