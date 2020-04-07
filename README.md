
# README

Before coding anything I started by reading the project description and then going through everything that was already done.

I then delineated what was missing, what had to be changed, and how most things should be implemented.

Not everything will be super robust, as I have finite time, but I hope this gives a basic view of my reasoning and codings skills.


## Backend Plan

### Schema

 - Big integer on primary keys

 - Money should not be stored as a FLOAT.
 Numeric(14, 4) complies with GAAP, but I prefer bigintegers (multiply by 100 on storage, divide by 100 on retrival)

 - Foreign Keys for Transactions <-> Accounts (To, From)

 - Currency table (name, iso code, maybe symbol, exchange rate relative to US dollar). I'm going to opt for a simplistic
 static currency exchange, with values being converted at transaction time. Account will a foreing key to currency.

 - Timestamps for accounts and transactions (especially transactions)

### Routes / Controllers

 - Move api.php routes to respective controllers, routes should be static

 - Input validation

 - Move logic to some other layers. For such a small app placing the db operations inside the models is fine.

 - API Resources

### Models

 - Relationships

    - Transaction belongs to 2x accounts (From, To).

    - Account has many transactions 2x (Transfered, Retrieved)

    - Account has one currency, currency belogs to many accounts.

  - Immutability for transactions. Once created a transaction can never be altered. I would prefer a datbase trigger, but I'll use model validation for simplicity.

### Integrity considerations

 - Money transfer should be an atomic operation, the withdrawl of the sender account and increment of the receiver
 one need to happen inside a transaction

 - Check if the sender has enough balance

 - Exchange conversion will only happen

 ### Security considerations

  - Remove CORS (especially with *) from production

  - Input validation and SQL Injection (previously mentioned)

  - CSRF? We don't have authentication so it doesn't really make sense.

  - Throttling? In a real scenarion everything would be under some form of authentication, so I don't see the need for it.


### Testing

  - List account transactions

  - List currencies

  - Move money (create transaction)

    - Valid transaction with == currencies 200

    - Valid transaction with != currencies 200

    - Overspending 400

    - Unexisting accounts (all variations)

  - Immutability of transactions (we should not be able to change them)

### Misc

 - Make the transaction operation a queued Job (but I'll probably call it synchronously for simplicity).

## Frontend plan

 - Select account ; List account transactions ; Create new transactions.

 - tsconfig.json target "es5"

 - move `_id.vue` to `_id/index.vue` https://nuxtjs.org/guide/routing/#dynamic-routes

 - Form validation

 - Table with pagination with proper URL rewriting

 - No need for any components since I'm doing something super simplistic

 - No need for vuex (we don't have any state)