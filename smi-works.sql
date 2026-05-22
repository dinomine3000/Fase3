# Privileges for `smi-works`@`localhost`

GRANT SELECT, INSERT, UPDATE, DELETE, FILE ON *.* TO `smi-works`@`localhost` IDENTIFIED BY PASSWORD '*538784F54B55D337498665699781BE6E3CF3B98B';

GRANT ALL PRIVILEGES ON `smi-works\_%`.* TO `smi-works`@`localhost`;