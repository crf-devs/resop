Feature:
    In order to fill in my availabilities,
    As a user,
    I must be able to log in.

    Scenario: As anonymous, I cannot go to the homepage
        When I go to the homepage
        Then I should be on "/login"
        And the response status code should be 200

    Scenario Outline: As a user with a password, I can log in with it
        Given I am on "/login"
        When I fill in the following:
            | user_login[identifier] | <login> |
            | user_login[password]   | covid19 |
        And I press "Je me connecte"
        Then I should be on "/"
        And the response status code should be 200
        And I should see "NIVOL : 990001A"
        Examples:
            | login              |
            | john.doe@resop.com |
            | 990001A            |

    Scenario Outline: As a user with a password, I cannot log in with an invalid one
        Given I am on "/login"
        When I fill in the following:
            | user_login[identifier] | <login> |
            | user_login[password]   | invalid |
        And I press "Je me connecte"
        Then I should be on "/login"
        And the response status code should be 400
        And I should see "Veuillez saisir un numéro NIVOL ou une adresse e-mail valide, ou la date de naissance ne corresponds pas à ce NIVOL/email."
        Examples:
            | login              |
            | john.doe@resop.com |
            | 990001A            |

    Scenario Outline: As a user with a password, I cannot log in with my birth date
        Given I am on "/login"
        When I fill in the following:
            | user_login[identifier]      | <login> |
            | user_login[birthday][day]   | 01      |
            | user_login[birthday][month] | 01      |
            | user_login[birthday][year]  | 1990    |
        And I press "Je me connecte"
        Then I should be on "/login"
        And the response status code should be 400
        And I should see "Veuillez saisir un numéro NIVOL ou une adresse e-mail valide, ou la date de naissance ne corresponds pas à ce NIVOL/email."
        Examples:
            | login              |
            | john.doe@resop.com |
            | 990001A            |

    Scenario Outline: As a user without a password, I can log in with my birth date
        Given I am on "/login"
        When I fill in the following:
            | user_login[identifier]      | <login> |
            | user_login[birthday][day]   | 01      |
            | user_login[birthday][month] | 01      |
            | user_login[birthday][year]  | 1990    |
        And I press "Je me connecte"
        Then I should be on "/"
        And the response status code should be 200
        And I should see "NIVOL : 990002A"
        Examples:
            | login              |
            | jane.doe@resop.com |
            | 990001A            |

    Scenario Outline: As a user without a password, I cannot log in with an invalid birth date
        Given I am on "/login"
        When I fill in the following:
            | user_login[identifier]      | <login> |
            | user_login[birthday][day]   | 02      |
            | user_login[birthday][month] | 02      |
            | user_login[birthday][year]  | 1992    |
        And I press "Je me connecte"
        Then I should be on "/login"
        And the response status code should be 400
        And I should see "Veuillez saisir un numéro NIVOL ou une adresse e-mail valide, ou la date de naissance ne corresponds pas à ce NIVOL/email."
        Examples:
            | login              |
            | jane.doe@resop.com |
            | 990001A            |

    Scenario: As an authenticated user, I can log out
        Given I am authenticated as "john.doe@resop.com"
        And I am on "/"
        When I follow "Déconnexion"
        Then I should be on "/login"
        And the response status code should be 200

    Scenario: As anonymous, when I try to log in with a non-existing account, I'm redirected to the registration page
        Given I am on "/login"
        When I fill in the following:
            | user_login[identifier]      | invalid@resop.com |
            | user_login[birthday][day]   | 01                |
            | user_login[birthday][month] | 01                |
            | user_login[birthday][year]  | 1990              |
        And I press "Je me connecte"
        Then I should be on "/user/new"
        And the response status code should be 200
        And the "user_emailAddress" field should contain "invalid@resop.com"

    Scenario: As anonymous, I cannot log in with empty data
        Given I am on "/login"
        When I press "Je me connecte"
        Then I should be on "/user/new"
        And the response status code should be 200
