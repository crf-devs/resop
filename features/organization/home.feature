Feature:
    In order to manage an organization,
    As an admin of an organization,
    I must be able to log in to my organization.

    Scenario: As anonymous, I cannot manage an organization
        When I go to "/organizations/201"
        Then I should be on "/login"
        And the response status code should be 200

    Scenario: As an admin of an organization, I can go to the homepage of my organization
        Given I am authenticated as "admin201@resop.com"
        And I am on the homepage
        When I follow "Gérer ma structure"
        Then I should be on "/organizations/201"
        And the response status code should be 200
        And I should see "DT75"

    Scenario Outline: As an admin of an organization but without a password, I cannot go to any page of my organization
        Given I am authenticated as "admin203@resop.com"
        When I go to "<url>"
        Then the response status code should be 403
        Examples:
            | url                     |
            | /organizations/203        |
            | /organizations/203/new    |
            | /organizations/203/search |
            | /organizations/203/edit   |
            | /organizations/203/assets |
            | /organizations/203/users  |
            | /organizations/planning |

    Scenario Outline: As an admin of an organization, I cannot go to the homepage of another organization
        Given I am authenticated as "admin201@resop.com"
        When I go to "<url>"
        Then the response status code should be 403
        Examples:
            | url              |
            | /organizations/2 |
            | /organizations/203 |
            | /organizations/204 |
