# TODO

- [ ] Спроектировать Entities для БД (User, Wallet, Transactions) + Мигрировать
- [ ] Базовые запросы в Репо для работы с Entity

src/
├── Controller/
│ ├── AuthController.php
│ ├── WalletController.php
│ └── TransactionController.php
├── Entity/
│ ├── User.php
│ ├── Wallet.php
│ └── Transaction.php
├── Repository/
│ ├── WalletRepository.php
│ └── TransactionRepository.php
├── UseCase/
│ ├── CreateTransaction/
│ │ ├── CreateTransactionUseCase.php
│ │ └── CreateTransactionDTO.php
│ └── ProcessTransaction/
│ ├── ProcessTransactionUseCase.php
│ └── ProcessTransactionDTO.php
├── Message/
│ ├── ProcessTransactionMessage.php
│ └── SendNotificationMessage.php
├── MessageHandler/ -> (Jobs)
│ ├── ProcessTransactionHandler.php
│ └── SendNotificationHandler.php
└── EventListener/
├── AuthListener.php
└── ReqResLogListener.php # уже готов
