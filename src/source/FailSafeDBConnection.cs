using System;
using System.Collections.Generic;
using System.Text;
using System.Threading;
using MySql.Data;
using MySql.Data.MySqlClient;
using System.Runtime.Remoting.Metadata.W3cXsd2001;

namespace PolyScan
{
    struct QueueEntry
    {
        public string sha256;
        public int id;
        public byte[] file;
    }

    class FailSafeDBConnection
    {
        const int DB_RETRY_TIME_SECONDS = 2;

        private string _ip;
        private string _user;
        private string _pass;
        private string _dbName;

        private MySqlConnection _db;

        private Semaphore _threadSlower;

        public static FailSafeDBConnection GetConnection(string ip, string user, string pass, string dbName)
        {
            return new FailSafeDBConnection(ip, user, pass, dbName);
        }

        public static FailSafeDBConnection GetConnection(FailSafeDBConnection toClone)
        {
            FailSafeDBConnection newConnection = toClone.GetClone();
            newConnection.EnsureConnected();
            return newConnection;
        }

        protected FailSafeDBConnection(string ip, string user, string pass, string dbName)
        {
            _ip = ip; _user = user; _pass = pass; _dbName = dbName;
            _threadSlower = new Semaphore(1,1);
            this.EnsureConnected();
        }
        
        //Returns true/false if connected
        public bool CheckConnection()
        {
            return _db.State == System.Data.ConnectionState.Open || _db.State == System.Data.ConnectionState.Fetching || _db.State == System.Data.ConnectionState.Executing;
        }

        //Block until connected. true if already connectd, false if had to reconnect
        public bool EnsureConnected()
        {
            bool wasConnected = true;
            while (_db == null || _db.State != System.Data.ConnectionState.Open)
            {
                wasConnected = false;
                Error.Warning("MySQL was not connected when EnsureConnected was called, trying to connect.");

                try
                {
                    string conString = "SERVER=" + _ip + ";" +
                        "DATABASE=" + _dbName + ";" +
                        "UID=" + _user + ";" +
                        "PASSWORD=" + _pass + ";";
                    if (_db != null)
                    {
                        _db.Dispose();
                        _db = null;
                    }
                    _db = new MySqlConnection(conString);
                    _db.Open();
                    Error.GoodMessage("MySQL connected.");
                }
                catch (MySqlException ex)
                {
                    Error.RecoverableError("Attempt to connect to MySQL FAILED: " + ex.Message + 
                        "\nRetrying in " + DB_RETRY_TIME_SECONDS.ToString() + " seconds...");
                    Thread.Sleep(DB_RETRY_TIME_SECONDS * 1000);
                }
            }
            return wasConnected;
        }

        protected FailSafeDBConnection GetClone()
        {
            return new FailSafeDBConnection(_ip, _user, _pass, _dbName);
        }

        #region "Scanner Specific Functions"

        public Queue<QueueEntry> GetWaitingQueue(int queueNum)
        {
            _threadSlower.WaitOne();
            restart:
            Queue<QueueEntry> currentQueue = new Queue<QueueEntry>();
            EnsureConnected();
            try
            {
                MySqlCommand command = _db.CreateCommand();
                MySqlDataReader Reader;
                command.CommandText = "SELECT * FROM queue WHERE queue='" + queueNum.ToString() + "' AND complete='0'";
                Reader = command.ExecuteReader();
                while (Reader.Read()) //loop thru items in the queue
                {
                    MySqlCommand update = _db.CreateCommand();
                    int current = (int)Reader.GetValue(0); //could be this if id isnt int :S
                    string sha256 = Reader["hash"].ToString();
                    string hexfile = (string)Reader["filedata"];
                    byte[] file = HexToByte(hexfile);

                    QueueEntry newEntry;
                    newEntry.file = file;
                    newEntry.id = current;
                    newEntry.sha256 = sha256;

                    currentQueue.Enqueue(newEntry);

                    update.CommandText = "UPDATE queue SET complete='2' WHERE id='" + current + "'";
                    update.ExecuteNonQuery();
                }
                Reader.Close();
            }
            catch (MySqlException ex)
            {
                Error.RecoverableError("MySQL Error in GetWaitingQueue: " + ex.Message + 
                    "\nRetrying in " + DB_RETRY_TIME_SECONDS.ToString() + " seconds...");
                Thread.Sleep(DB_RETRY_TIME_SECONDS * 1000);
                goto restart;
            }
            _threadSlower.Release();
            return currentQueue;
        }

        //TODO: Performance check and improvements.
        private byte[] HexToByte(string hex)
        {
            if (hex.Length % 2 == 1)
            {
                Error.RecoverableError("Malformed HEX string (the one that contains the binary to scan)");
                hex = hex + '0';
            }

            //This should be faster..
            SoapHexBinary shb = SoapHexBinary.Parse(hex);
            return shb.Value;

            //If that doesn't compile/work, fall back to this: (slower)
            /*if (hex == null)
                return null;



            byte[] data = new byte[hex.Length / 2];

            for (int i = 0; i < data.Length; i++)
                data[i] = Convert.ToByte(hex.Substring(i * 2, 2), 16);

            return data;*/
        }

        public int GetNextUpdateTime()
        {
            _threadSlower.WaitOne();
        restart:
            int time = 0;
            EnsureConnected();
            try
            {
                MySqlCommand command = _db.CreateCommand();
                MySqlDataReader Reader;
                command.CommandText = "SELECT * FROM `settings`";
                Reader = command.ExecuteReader();
                if (Reader.Read())
                {
                    time = (int)Reader["nexttime"];
                }
                Reader.Close();
            }
            catch(MySqlException ex)
            {
                Error.RecoverableError("MySQL Error in GetNextUpdateTime: " + ex.Message +
    "\nRetrying in " + DB_RETRY_TIME_SECONDS.ToString() + " seconds...");
                Thread.Sleep(DB_RETRY_TIME_SECONDS * 1000);
                goto restart;
            }
            _threadSlower.Release();
            return time;
        }

        public void SetUpdateTime(int finished, int next)
        {
            _threadSlower.WaitOne();
        restart:
            EnsureConnected();
            try
            {
                MySqlCommand command = _db.CreateCommand();
                command.CommandText = "UPDATE `settings` SET nexttime='" + next.ToString() + "', lasttime='" + finished.ToString() + "'"; ;
                command.ExecuteNonQuery();
            }
            catch (MySqlException ex)
            {
                Error.RecoverableError("MySQL Error in SetUpdateTime: " + ex.Message +
                                    "\nRetrying in " + DB_RETRY_TIME_SECONDS.ToString() + " seconds...");
                Thread.Sleep(DB_RETRY_TIME_SECONDS * 1000);
                goto restart;
            }
            _threadSlower.Release();
        }

        public void SetScanResult(int id, AvType type, string scanResult)
        {
            _threadSlower.WaitOne();
        restart:
            EnsureConnected();
            try
            {
                MySqlCommand command = _db.CreateCommand();
                command.CommandText = "UPDATE queue SET " + type.ToString() + "='" + scanResult + "' WHERE id='" + id.ToString() + "'"; ;
                command.ExecuteNonQuery();
            }
            catch (MySqlException ex)
            {
                Error.RecoverableError("MySQL Error in SetScanResult: " + ex.Message +
                                    "\nRetrying in " + DB_RETRY_TIME_SECONDS.ToString() + " seconds...");
                Thread.Sleep(DB_RETRY_TIME_SECONDS * 1000);
                goto restart;
            }
            _threadSlower.Release();
        }
            
        public void SetScannerUpdating(AvType type)
        {
            _threadSlower.WaitOne();
        restart:
            EnsureConnected();
            try
            {
                MySqlCommand command = _db.CreateCommand();
                command.CommandText = "UPDATE settings SET " + type.ToString() + "='1'";
                command.ExecuteNonQuery();
            }
            catch (MySqlException ex)
            {
                Error.RecoverableError("MySQL Error in SetScannerUpdating: " + ex.Message +
                                    "\nRetrying in " + DB_RETRY_TIME_SECONDS.ToString() + " seconds...");
                Thread.Sleep(DB_RETRY_TIME_SECONDS * 1000);
                goto restart;
            }
            _threadSlower.Release();
        }

        public void SetStatusUpdating()
        {
            _threadSlower.WaitOne();
        restart:
            EnsureConnected();
            try
            {
                MySqlCommand command = _db.CreateCommand();
                command.CommandText = "UPDATE settings SET complete='1'";
                command.ExecuteNonQuery();
            }
            catch (MySqlException ex)
            {
                Error.RecoverableError("MySQL Error in SetStatusUpdating: " + ex.Message +
                                    "\nRetrying in " + DB_RETRY_TIME_SECONDS.ToString() + " seconds...");
                Thread.Sleep(DB_RETRY_TIME_SECONDS * 1000);
                goto restart;
            }
            _threadSlower.Release();
        }

        public void SetStatusNotUpdating()
        {
            _threadSlower.WaitOne();
        restart:
            EnsureConnected();
            try
            {
                MySqlCommand command = _db.CreateCommand();
                command.CommandText = "UPDATE settings SET complete='0'";
                command.ExecuteNonQuery();
            }
            catch (MySqlException ex)
            {
                Error.RecoverableError("MySQL Error in SetStatusNotUpdating: " + ex.Message +
                                    "\nRetrying in " + DB_RETRY_TIME_SECONDS.ToString() + " seconds...");
                Thread.Sleep(DB_RETRY_TIME_SECONDS * 1000);
                goto restart;
            }
            _threadSlower.Release();
        }

        public void SetScannerToBeUpdated(AvType scanner)
        {
            _threadSlower.WaitOne();
        restart:
            EnsureConnected();
            try
            {
                MySqlCommand command = _db.CreateCommand();
                command.CommandText = "UPDATE settings SET " + scanner.ToString() + "='2'";
                command.ExecuteNonQuery();
            }
            catch (MySqlException ex)
            {
                Error.RecoverableError("MySQL Error in SetScannerToBeUpdated: " + ex.Message +
                                    "\nRetrying in " + DB_RETRY_TIME_SECONDS.ToString() + " seconds...");
                Thread.Sleep(DB_RETRY_TIME_SECONDS * 1000);
                goto restart;
            }
            _threadSlower.Release();
        }

        public void SetScannerDoneUpdating(AvType scanner)
        {
            _threadSlower.WaitOne();
        restart:
            EnsureConnected();
            try
            {
                MySqlCommand command = _db.CreateCommand();
                command.CommandText = "UPDATE settings SET " + scanner.ToString() + "='0'";
                command.ExecuteNonQuery();
            }
            catch (MySqlException ex)
            {
                Error.RecoverableError("MySQL Error in SetScannerDoneUpdating: " + ex.Message +
                                    "\nRetrying in " + DB_RETRY_TIME_SECONDS.ToString() + " seconds...");
                Thread.Sleep(DB_RETRY_TIME_SECONDS * 1000);
                goto restart;
            }
            _threadSlower.Release();
        }


        #endregion
    }
}
