
CREATE trigger [dbo].[CNC_Running_Data_Get]
ON [dbo].[CNCStatusData]
AFTER UPDATE
AS

DECLARE @OLDMachineName nvarchar(30)
SELECT @OLDMachineName = MachineName FROM deleted;
DECLARE @OLDLogDate DATETIME, @NEWLogDate DATETIME
SELECT @OLDLogDate = LogDate FROM deleted;
SELECT @NEWLogDate = LogDate FROM inserted;
DECLARE @OLDSpindleSpeed1 INT, @NEWSpindleSpeed1 INT
SELECT @OLDSpindleSpeed1 = SpindleSpeed1 FROM deleted;
SELECT @NEWSpindleSpeed1 = SpindleSpeed1 FROM inserted;
DECLARE @OLDSpindleLoad1 INT, @NEWSpindleLoad1 INT
SELECT @OLDSpindleLoad1 = SpindleLoad1 FROM deleted;
SELECT @NEWSpindleLoad1 = SpindleLoad1 FROM inserted;


IF (@NEWSpindleSpeed1 > 1 OR @NEWSpindleLoad1 > 1)
BEGIN
INSERT INTO CNCChart_RunningData (MachineName, LogDate, SpindleSpeed1, SpindleLoad1)
VALUES (@OLDMachineName, DATEADD(SECOND,-5,GETDATE()), 0, 0)
INSERT INTO CNCChart_RunningData (MachineName, LogDate, SpindleSpeed1, SpindleLoad1)
VALUES (@OLDMachineName, @NEWLogDate, @NEWSpindleSpeed1, @NEWSpindleLoad1)
INSERT INTO CNCChart_RunningData (MachineName, LogDate, SpindleSpeed1, SpindleLoad1)
VALUES (@OLDMachineName, DATEADD(SECOND,5,GETDATE()), 0, 0)
END
